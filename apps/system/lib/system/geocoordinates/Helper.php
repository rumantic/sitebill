<?php
namespace system\lib\system\geocoordinates;

class Helper {

    /**
     * Convert string 'lat1,lng1,lat2,lng2, ...,latN,lngN' to geopoints array [[lat1,lng1], [lat2,lng2], ..., [latN,lngN]]
     * @param string $polylineString
     * @return array
     */
    public static function fromStringToPolylineArrayPairs($polylineString)
    {
        $polylineStringArray = explode(',', $polylineString);
        $count = count($polylineStringArray);
        for ($i = 0; $i <= $count; $i += 2) {
            if(isset($polylineStringArray[$i+1])){
                $polylineStringPairs[] = [$polylineStringArray[$i], $polylineStringArray[$i+1]];
            }
        }
        return $polylineStringPairs;
    }

    /**
     * Convert comma separated string to semicolon separated string
     * 'lat1,lng1,lat2,lng2, ...,latN,lngN' => 'lat1,lng1;lat2,lng2; ...;latN,lngN'
     * @param string $polylineString
     * @return string
     */
    public static function fromStringToPolylineStringPairs($polylineString)
    {
        $polylineStringArray = explode(',', $polylineString);
        for ($i = 0; $i < count($polylineStringArray); $i++) {
            $polylineStringArrayPairs[] = $polylineStringArray[$i] . ',' . $polylineStringArray[++$i];
        }
        $polylineStringPairs = implode(';', $polylineStringArrayPairs);
        return $polylineStringPairs;
    }

    /**
     * Return polygon bounds and array of lines
     * @param array $points [[lat, lng], [lat, lng], [lat, lng]...]
     * @return array
     */
    public static function getPolygoneLinesAndBounds($points){
        $lines = [];
        $count = count($points);
        $i = 0;
        $max_lat = false;
        $min_lat = false;
        $max_lng = false;
        $min_lng = false;
        foreach ($points as $k => $point){
            $lines[$k]['s']['lat'] = $point[0];
            $lines[$k]['s']['lng'] = $point[1];
            $lines[$k]['e']['lat'] = $points[$k+1][0];
            $lines[$k]['e']['lng'] = $points[$k+1][1];
            $delta_lat = $lines[$k]['e']['lat'] - $lines[$k]['s']['lat'];
            $delta_lng = $lines[$k]['e']['lng'] - $lines[$k]['s']['lng'];
            if($delta_lng == 0){
                $lines[$k]['type'] = 'v';
                $koef = 0;
            }elseif($delta_lat == 0){
                $lines[$k]['type'] = 'h';
                $koef = 0;
            }else{
                $lines[$k]['type'] = 'c';
                $koef = ($delta_lat)/($delta_lng);
            }

            $lines[$k]['koef'] = $koef;
            if($lines[$k]['type'] == 'c'){
                $lines[$k]['ckoef'] = $lines[$k]['s']['lat'] - ($koef * $lines[$k]['s']['lng']);
            }else{
                $lines[$k]['ckoef'] = 0;
            }

            if($max_lat !== false && $point[0] > $max_lat){
                $max_lat = $point[0];
            }elseif($max_lat === false){
                $max_lat = $point[0];
            }
            if($min_lat !== false && $point[0] < $min_lat){
                $min_lat = $point[0];
            }elseif($min_lat === false){
                $min_lat = $point[0];
            }
            if($max_lng !== false && $point[1] > $max_lng){
                $max_lng = $point[1];
            }elseif($max_lng === false){
                $max_lng = $point[1];
            }
            if($min_lng !== false && $point[1] < $min_lng){
                $min_lng = $point[1];
            }elseif($min_lng === false){
                $min_lng = $point[1];
            }
            $i++;
            if($i == $count-1){
                break;
            }
        }

        return [
            'bounds' => [
                'min_lat' => $min_lat,
                'min_lng' => $min_lng,
                'max_lat' => $max_lat,
                'max_lng' => $max_lng
            ],
            'lines' => $lines
        ];
    }

    /**
     * Check is number is between two numbers $fp1 and $fp2
     * @param float $point
     * @param float $fp1
     * @param float $fp2
     * @return bool
     */
    public static function isBetween($point, $fp1, $fp2){
        $start = $fp1;
        if($fp2 < $start){
            $start = $fp2;
            $end=$fp1;
        }else{
            $end=$fp2;
        }
        if($point >= $start && $point <= $end){
            return true;
        }
        return false;
    }

    /**
     * Check is point is inside of polygon
     * @param array $point [lat, lng]
     * @param array $lines
     * @return bool
     */
    public static function isInRegion($point, $lines){
        $point_lat = $point['lat'];
        $point_lng = $point['lng'];

        foreach($lines as $line){
            if($line['type'] == 'v' && self::isBetween($point_lat, $line['s']['lat'], $line['e']['lat']) && $point_lng == $line['s']['lng']){
                return true;
            }elseif($line['type'] == 'h' && self::isBetween($point_lng, $line['s']['lng'], $line['e']['lng']) && $point_lat == $line['s']['lat']){
                return true;
            }
        }

        $intersectCount = 0;

        foreach($lines as $line){
            if($line['type'] == 'v'){

            }elseif($line['type'] == 'h' && self::isBetween($point_lng, $line['s']['lng'], $line['e']['lng']) && $point_lat < $line['s']['lat']){
                $intersectCount++;
            }else{
                if(self::isBetween($point_lng, $line['s']['lng'], $line['e']['lng'])){
                    $intersect_lat = $line['koef']*$point_lng+$line['ckoef'];
                    if($intersect_lat >= $point_lat){
                        $intersectCount++;
                    }
                }
            }
        }

        if($intersectCount == 0){
            return false;
        }
        if($intersectCount == 1){
            return true;
        }
        if($intersectCount % 2 == 0){
            return false;
        }
        return true;
    }

}