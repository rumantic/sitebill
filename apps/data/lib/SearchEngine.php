<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

/**
 * Created by PhpStorm.
 * User: unclead.nsk
 * Date: 06.04.14
 * Time: 15:09
 */

require_once 'Helper.php';

class SearchEngine
{
    const HASH_DIVIDER = 25;

    const DATA_LIMIT = 5000;

    private $search_text = null;

    private $stop_words = array();

    private $stop_symbols = array();

    private $dataSource = null;

    private $shingle_length = 10;

    private $use_divider = false;

    private $percent = 0;

    private $duplicates = 0;

    public function __construct(DataSourceInterface $dataSource, $options = array())
    {
        $this->dataSource = $dataSource;

        if (isset($options['stop_words'])) {
            $this->setStopWords($options['stop_words']);
        }

        if (isset($options['stop_symbols'])) {
            $this->setStopWords($options['stop_symbols']);
        }

        if (isset($options['shingle_length'])) {
            $this->setShingleLength($options['shingle_length']);
        }
    }

    /**
     * Установить длины шингла
     * @param $shingle_length
     */
    public function setShingleLength($shingle_length)
    {
        $this->shingle_length = $shingle_length;
    }

    /**
     * Получить длину шингла
     * @return int
     *
     */
    public function getShingleLength()
    {
        return $this->shingle_length;
    }

    /**
     * Установить список стоп-слов
     * @param $stop_words
     */
    public function setStopWords($stop_words)
    {
        $stop_words = is_array($stop_words) ? $stop_words : explode(',', $stop_words);

        foreach ($stop_words as &$word) {
            $word = Helper::toLowerCase($word);
        }

        $this->stop_words = array_unique($stop_words);
    }

    /**
     * Получить список стоп-слов
     * @return array
     */
    public function getStopWords()
    {
        return $this->stop_words;
    }

    /**
     * Установить список стоп-символов
     * @param $stop_symbols
     */
    public function setStopSymbols($stop_symbols)
    {
        $stop_symbols = is_array($$stop_symbols) ? $stop_symbols : explode(',', $stop_symbols);

        foreach ($stop_symbols as $i => $symbol) {
            if (empty($symbol)) {
                unset($stop_symbols[$i]);
            }
        }

        $this->stop_symbols = $stop_symbols;
    }

    /**
     * Получить список стоп-символов
     * @return array
     */
    public function getStopSymbols()
    {
        return $this->stop_symbols;
    }

    /**
     * Установить анализируемый текст
     * @param $text
     */
    public function setSearchText($text)
    {
        $this->search_text = $text;
    }

    public function getSearchText()
    {
        return $this->search_text;
    }

    /**
     * Выполнение поиска неточных совпадений текста среди набора данных из источника данных
     * @throws Exception
     */
    public function run()
    {
        ini_set('max_execution_time', 600);

        if (empty($this->search_text)) {
            throw new Exception('Analyzed text can not be empty');
        }

        Helper::resetTimer();
        $search_text = $this->canonizeText($this->getSearchText());

        $shingles = $this->populateShingles($search_text);

        $total_count = $this->dataSource->getCount();

        $processed_count = 0;

        // В случае, если размер выборки больше допустимого предела
        // используем цикл со смещением, чтобы избежать проблемм с переполнением памяти
        if($total_count > self::DATA_LIMIT) {

            $offset = 0;
            $this->dataSource->setCount(self::DATA_LIMIT);

            while($offset < $total_count) {
                $this->dataSource->setOffset($offset);
                $data = $this->dataSource->getData(true);

                $this->compareData($shingles, $data);

                $processed_count += count($data);
                $offset += self::DATA_LIMIT;
            }
        } else {
            $data = $this->dataSource->getData(true);
            $processed_count += count($data);
            $this->compareData($shingles, $data);
        }



        $this->percent = round($this->percent / $processed_count, 2);

        $result = array(
            'percent'       => $this->percent,
            'duplicates'    => $this->duplicates,
            'time'          => Helper::timer(true)
        );

        return $returnAjax ? json_encode($result) : $result;
    }

    /**
     * Сравниваем пачку данных с исходным текстом
     * @param $shingles
     * @param $data
     */
    private function compareData($shingles, $data) {
        foreach ($data as $text) {
            $source_text = $this->canonizeText($text);
            $source_shingles = $this->populateShingles($source_text);

            $intersect = array_intersect($shingles, $source_shingles);
            $merge = array_unique(array_merge($shingles, $source_shingles));

            $diff = round((count($intersect) / count($merge)) / 0.01, 2);

            $this->percent += $diff;

            if ($diff == 100) {
                $this->duplicates++;
            }
        }
    }

    /**
     * Подготавливаем текст
     * Для этого:
     * - удаляем стоп-символы
     * - удаляем стоп-слова
     * - приводим все к нижнему регистру
     *
     * @see http://habrahabr.ru/post/65944
     *
     * @param $text
     * @return mixed|string
     */
    private function canonizeText($text)
    {
        $text = trim($text);
        $text = Helper::toLowerCase($text);
        $text = $this->stripHtmlTags($text);
        $text = $this->replaceStopSymbols($text);
        $text = $this->replaceStopWords($text);
        $text = $this->clearMultiSpaces($text);
        $text = trim($text);
        return $text;
    }


    /**
     * Очищаем текст от html-тагов
     * @see http://www.php.net/manual/ru/function.strip-tags.php#68757
     */
    private function stripHtmlTags($text)
    {
        $search = array(
            '@<script[^>]*?>.*?</script>@si', // Strip out javascript
            '@<[\/\!]*?[^<>]*?>@si', // Strip out HTML tags
            '@<style[^>]*?>.*?</style>@siU', // Strip style tags properly
            '@<![\s\S]*?--[ \t\n\r]*>@' // Strip multi-line comments including CDATA
        );
        return preg_replace($search, '', $text);
    }

    /**
     * Замена стоп-символов в тексте
     * @param $text
     * @return mixed
     */
    private function replaceStopSymbols($text)
    {
        $stop_symbols = $this->getStopSymbols();

        // Если не указан список спец. символов, то заменяем убираем из текста все что не буква/цифра/пробел
        if (empty($stop_symbols)) {
            $pattern = '/[^a-zA-Z 0-9а-яА-Я]+/';
            $pattern .= Helper::isUnicode($text) ? 'u' : '';

            return preg_replace($pattern, ' ', $text);
        } else {
            //@todo напистаь обработчик для замены стоп символов
        }
    }


    /**
     * Замена стоп-слов в тексте
     * @param $text
     * @return mixed
     */
    private function replaceStopWords($text)
    {
        $stop_words = $this->getStopWords();

        if (!empty($stop_words)) {
            $pattern = '/\b(' . implode('|', $this->stop_words) . ')\b/';

            // @todo возможно стоит 1 раз определить признак и использовать уже значение переменной в коде
            $pattern .= Helper::isUnicode($text) ? 'u' : '';

            return preg_replace($pattern, '', $text);
        }
    }

    /**
     * Заменяем все разделите встречающиеся 1 и более раз на пробел
     * @param $text
     * @return mixed
     */
    private function clearMultiSpaces($text)
    {
        $pattern = '/\s+/';
        $pattern .= Helper::isUnicode($text) ? 'u' : '';
        return preg_replace($pattern, ' ', $text);
    }

    /**
     * Формируем набор шинглов
     * @param $text
     * @return array
     */
    public function populateShingles($text)
    {
        $elements = explode(" ", $text);

        $count = count($elements);

        $shingle_length = $this->getShingleLength();

        // В случае, если количество слов в тексте меньше минимальной длины шингла
        // мы устанавливаем длину шингла равную этому значению
        if ($count > $this->shingle_length) {
            $count = $count - $this->shingle_length + 1;
        } else {
            $shingle_length = $count;
        }

        $shingles = array();
        $shingles_hash = array();

        for ($i = 0; $i < $count; $i++) {
            $shingle = implode(" ", array_slice($elements, $i, $shingle_length));
            $shingles[] = $shingle;

            $hash = crc32($shingle);

            /**
             * При обработке больших текстов можно для сравнения использовать
             * лишь те шинглы, хэш которых кратен делителю от 10 до 40
             *
             */
            if($this->use_divider) {
                if($hash % self::HASH_DIVIDER == 0) {
                    $shingles_hash[] = $hash;
                }          
            } else {
                $shingles_hash[] = $hash;
            }
        }

        return $shingles_hash;
    }

}