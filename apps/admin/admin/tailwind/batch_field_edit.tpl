<script>
    var vals={$field_vals};
</script>

<fieldset class="step perc_diff wedit_changerform_tpl">
    <legend>Процентное изменение <a href="#">(удалить)</a></legend>
    <span class="help-block">Процентное изменение значения. Указывайте значения процентов изменения значения и выбирайте направление - увеличение или уменьшение</span>
    <input type="text" value="" name="step[][perc_diff]" class="stepval">
    <select name="step[][perc_diff_dir]" class="stepdir">
        <option value="plus">Увеличить</option>
        <option value="minus">Уменьшить</option>
    </select>    
</fieldset>

<fieldset class="step round wedit_changerform_tpl">
    <legend>Дискретизация <a href="#">(удалить)</a></legend>
    <span class="help-block">Указывает степень дискретизации чисел (округления). Например, укажите значение "10000" если хотите дискретизировать значения цен до десятков тысяч числа</span>
    <input type="text" value="" name="step[][round]" class="stepval">
    <select name="step[][round_dir]" class="stepdir">
        <option value="near">К ближайшему</option>
        <option value="min">К меньшему</option>
        <option value="max">К большему</option>
    </select>
</fieldset>

<fieldset class="step summ_diff wedit_changerform_tpl">
    <legend>Изменение суммы <a href="#">(удалить)</a></legend>
    <span class="help-block">Увеличение\уменьшение значения на определенную величину. Указывайте значения на которые Вы хотели бы изменить значение цены. Оно будет прибавлено или отнято.</span>
    <input type="text" value="" name="step[][summ_diff]" class="stepval">
    <select name="step[][summ_diff_dir]" class="stepdir">
        <option value="plus">Увеличить</option>
        <option value="minus">Уменьшить</option>
    </select>
</fieldset>

<div class="row-fluid">
    <div class="span6">
        <form id="wedit_changerform-form" method="post" action="{$estate_folder}/admin/">
            <div id="wedit_changerform">
                <span class="help-block">Выберите необходимую категорию внутри которой будут применены трансформации</span>
                {$structure_box}
                <span class="help-block">Управляйте значением поля путем применения трансформаций. Трансформации применяются пошагово в порядке их добавления. Для удаления ненужной трансформации используйте ссылку "удалить" возле ее заголовка</span>
                <span class="help-block">Для просмотра результата используйте кнопку "Пересчитать" и таблицу справа</span>
                <div id="wedit_changerform_ctrl">
                    <p><a data-type="perc_diff" href="#">Добавить трансформацию процентного изменения</a></p>
                    <p><a data-type="round" href="#">Добавить трансформацию одискретизации (округления)</a></p>
                    <p><a data-type="summ_diff" href="#">Добавить трансформацию изменения по значению</a></p>
                </div>
                <div class="wedit_changerform_holder"></div>
                <button class="btn btn-primary">Пересчитать</button>
                <input class="btn btn-warning" type="submit" value="Применить изменения">
            </div>
            <input type="hidden" name="action" value="data">
            <input type="hidden" name="do" value="batch_field_edit">
            <input type="hidden" name="field" value="{$field_name}">
            {foreach from=$ids item=id}
                <input type="hidden" name="id[]" value="{$id}">
            {/foreach}
        </form>
    </div>
    <div class="span6">
        <table class="table table-hover" id="wedit_result">
            <thead><tr><th>Исходное</th><th>Результат</th></tr></thead>
            <tbody></tbody>
        </table>
    </div>
</div>


{literal}
<style>
.wedit_changerform_tpl {
    display: none;
}
</style>
<script>
    var step_count=0;
    var Modder={};
    Modder.vals = vals;
    Modder.recalc = function(){
        var t=$('#wedit_result');
        t.find('tbody').html('');
        for(var i in Modder.vals){
            np=Modder.goThrowSteps(Number(Modder.vals[i]));
            t.find('tbody').append($('<tr><td>'+Modder.number_format(Modder.vals[i], 0, ',', ' ')+'</td><td>'+Modder.number_format(np, 0, ',', ' ')+'</td></tr>')) ;
        }
    };
    Modder.goThrowSteps = function(sum){
        $('#wedit_changerform .step').each(function(){
            var _this=$(this);
            if(_this.hasClass('perc_diff')){
                sum=Modder.recalcPercDiff(sum, _this);
            }else if(_this.hasClass('round')){
                sum=Modder.recalcRound(sum, _this);
            }else if(_this.hasClass('summ_diff')){
                sum=Modder.recalcSummDiff(sum, _this);
            }
        });
        return sum;
    };
    Modder.recalcPercDiff = function(sum, el){
        var val=Number(el.find('.stepval').val());
        var dir=el.find('.stepdir').val();
        if(dir!='minus'){
            dir='plus';
        }
        if(!isNaN(val)){
            if(dir=='minus'){
                sum=sum-(sum*val/100);
            }else{
                sum=sum+(sum*val/100);
            }
        }
        return sum;
    };
    Modder.recalcRound = function(sum, el){
        var val=Number(el.find('.stepval').val());
        var dir=el.find('.stepdir').val();
        if(dir!='max' && dir!='min'){
            dir='near';
        }
        var k=1;
        if(!isNaN(val) && val>0){
            switch(dir){
                case 'max' : {
                   k=Math.ceil(sum/val);
                   break;
                }
                case 'min' : {
                   k=Math.floor(sum/val);
                   break;
                }
                case 'near' : {
                    if((sum%val)>=val/2){
                        k=Math.floor(sum/val)+1;
                    }else{
                        k=Math.floor(sum/val);
                    }
                   break;
                }
            }
            sum=k*val;
        }
        return sum;
    };
    Modder.recalcSummDiff = function(sum, el){
        var val=Number(el.find('.stepval').val());
        var dir=el.find('.stepdir').val();
        if(dir!='minus'){
            dir='plus';
        }
        if(!isNaN(val)){
            if(dir=='minus'){
                sum=sum-val;
            }else{
                sum=sum+val;
            }
        }
        return sum;
    };
    Modder.number_format = function(number, decimals, dec_point, thousands_sep) {
	  number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
	  var n = !isFinite(+number) ? 0 : +number,
		prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
		sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
		dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
		s = '',
		toFixedFix = function (n, prec) {
		  var k = Math.pow(10, prec);
		  return '' + Math.round(n * k) / k;
		};
	  // Fix for IE parseFloat(0.55).toFixed(0) = 0;
	  s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
	  if (s[0].length > 3) {
		s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
	  }
	  if ((s[1] || '').length < prec) {
		s[1] = s[1] || '';
		s[1] += new Array(prec - s[1].length + 1).join('0');
	  }
	  return s.join(dec);
	}
    
    $(document).ready(function(){
        $('#wedit_changerform-form').submit(function(){
            if($('#wedit_changerform .wedit_changerform_holder .step').length==0){
                return false;
            }else if(confirm('Изменения будут иметь необратимый характер. Вы действительно хотите применить изменения ко всем объектам по выбранному условию?')){
                return true;
            }
            return false;
        });
        $('#wedit_changerform button').click(function(e){
            e.preventDefault();    
            Modder.recalc();
        });
        $('#wedit_changerform_ctrl a').click(function(e){
            e.preventDefault();
            var type=$(this).data('type');
            var clone=$('.'+type+'.wedit_changerform_tpl').clone();
            clone.find('select, input').each(function(){
                $(this).attr('name', $(this).attr('name').replace('step[]', 'step['+step_count+']'));
            });
            step_count+=1;
            $('#wedit_changerform .wedit_changerform_holder').append(clone.removeClass('wedit_changerform_tpl'));
        });
        
        $(document).on('click', '.step legend a', function(e){
            e.preventDefault();
            $(this).parents('.step').remove();
        });
        Modder.recalc();
    });
</script> 
{/literal}