<?php
class Sitebill_Datetime {

	private static $formattypes=array(
			'standart'=>'yyyy-MM-dd',
			'eu'=>'dd/MM/yyyy',
			'us'=>'MM/dd/yyyy',
	);
	
	private static $dateFormat='standart';
	
	public static function setDateFormat($format){
		self::$dateFormat=$format;
	}
	
	public static function getFormats(){
		return self::$formattypes;
	}
	
	public static function getNowDatetime(){
		$formattypes=self::$formattypes;
		$inFormFormat=self::$dateFormat;
			
		if(!isset($formattypes[$inFormFormat])){
			$inFormFormat='standart';
		}
		switch($inFormFormat){
			case 'standart' : {
				return date('Y-m-d H:i:s', time());
				break;
			}
			case 'eu' : {
				return date('d/m/Y H:i:s', time());
				break;
			}
			case 'us' : {
				return date('m/d/Y H:i:s', time());
				break;
			}
		}
	}
	
	public static function getNowDate(){
		$formattypes=self::$formattypes;
		$inFormFormat=self::$dateFormat;
			
		if(!isset($formattypes[$inFormFormat])){
			$inFormFormat='standart';
		}
		switch($inFormFormat){
			case 'standart' : {
				return date('Y-m-d', time());
				break;
			}
			case 'eu' : {
				return date('d/m/Y', time());
				break;
			}
			case 'us' : {
				return date('m/d/Y', time());
				break;
			}
		}
	}
	
	public static function getNowTime(){
		return date('H:i:s', time());
	}

	public static function checkDTDatetime($vl, $parameters=array()){
			
		$formattypes=self::$formattypes;
		$inFormFormat=self::$dateFormat;	
			
		if(!isset($formattypes[$inFormFormat])){
			$inFormFormat='standart';
		}
			

		list($valueDate, $valueTime)=explode(' ', $vl);
		$result=true;
		
		list($Y, $M, $D)=explode('-', $valueDate);
/*
		switch($inFormFormat){
			case 'standart' : {
				list($Y, $M, $D)=explode('-', $valueDate);
				break;
			}
			case 'eu' : {
				list($D, $M, $Y)=explode('/', $valueDate);
				break;
			}
			case 'us' : {
				list($M, $D, $Y)=explode('/', $valueDate);
				break;
			}
		}*/
		list($H, $I, $S)=explode(':', $valueTime);
		
		if((int)$M>12 || (int)$M<1){
			$result=false;
		}
		if((int)$D>31 || (int)$D<1){
			$result=false;
		}
		if((int)$Y==0){
			$result=false;
		}
		
		if((int)$H>23 || (int)$H<0){
			$result=false;
		}
		if((int)$I>59 || (int)$I<0){
			$result=false;
		}
		if($parameters['noSeconds']!=1){
			if((int)$S>59 || (int)$S<0){
				$result=false;
			}
		}
		return $result;
	}
	
	public static function checkDTDate($vl, $parameters=array()){
			
		$formattypes=self::$formattypes;
		$inFormFormat=self::$dateFormat;
			
		if(!isset($formattypes[$inFormFormat])){
			$inFormFormat='standart';
		}
			
		$valueDate=$vl;
		$valueTime='00:00:00';
		$result=true;
	
		list($Y, $M, $D)=explode('-', $valueDate);
	/*
		switch($inFormFormat){
			case 'standart' : {
				list($Y, $M, $D)=explode('-', $valueDate);
				break;
			}
			case 'eu' : {
				list($D, $M, $Y)=explode('/', $valueDate);
				break;
			}
			case 'us' : {
				list($M, $D, $Y)=explode('/', $valueDate);
				break;
			}
		}*/
		if((int)$M>12 || (int)$M<1){
			$result=false;
		}
		if((int)$D>31 || (int)$D<1){
			$result=false;
		}
		if((int)$Y==0){
			$result=false;
		}
		return $result;
	}
	
	public static function checkDTTime($vl, $parameters=array()){
			
		
		$valueTime=$vl;
		$result=true;
		list($H, $I, $S)=explode(':', $valueTime);
		if((int)$H>23 || (int)$H<0){
			$result=false;
		}
		if((int)$I>59 || (int)$I<0){
			$result=false;
		}
		if($parameters['noSeconds']!=1){
			if((int)$S>59 || (int)$S<0){
				$result=false;
			}
		}
		
		return $result;
	}

	public static function getDatetimeCanonicalFromFormat($vl, $parameters=array()){
		$formattypes=self::$formattypes;
		$inFormFormat=self::$dateFormat;

		if(!isset($formattypes[$inFormFormat])){
			$inFormFormat='standart';
		}
		
		list($valueDate, $valueTime)=explode(' ', $vl);
		
		switch($inFormFormat){
			case 'standart' : {
				list($Y, $M, $D)=explode('-', $valueDate);
				break;
			}
			case 'eu' : {
				list($D, $M, $Y)=explode('/', $valueDate);
				break;
			}
			case 'us' : {
				list($M, $D, $Y)=explode('/', $valueDate);
				break;
			}
		}
		list($H, $I, $S)=explode(':', $valueTime);
		
		
		if(isset($parameters['noSeconds']) && $parameters['noSeconds']==1){
			$S='00';
		}
		$vl=$Y.'-'.$M.'-'.$D.' '.$H.':'.$I.':'.$S;
		
		
		return $vl;
	}
	
	public static function getDateCanonicalFromFormat($vl, $parameters=array()){
		$formattypes=self::$formattypes;
			
	
			
		$inFormFormat=self::$dateFormat;
	
		if(!isset($formattypes[$inFormFormat])){
			$inFormFormat='standart';
		}
		
		//$valueDate=$vl;
		list($valueDate, $valueTime)=explode(' ', $vl);
		if(!isset($valueTime)){
			$valueTime='00:00:00';
		}
		
		
		switch($inFormFormat){
			case 'standart' : {
				list($Y, $M, $D)=explode('-', $valueDate);
				break;
			}
			case 'eu' : {
				list($D, $M, $Y)=explode('/', $valueDate);
				break;
			}
			case 'us' : {
				list($M, $D, $Y)=explode('/', $valueDate);
				break;
			}
		}
		
		$vl=$Y.'-'.$M.'-'.$D.' '.$valueTime;
	
		return $vl;
	}
	
	public static function getTimeCanonicalFromFormat($vl, $parameters=array()){
		
		$valueDate='0000-00-00';
		$valueTime=$vl;
		list($H, $I, $S)=explode(':', $valueTime);
		
		if($parameters['noSeconds']==1){
			$S='00';
		}
		$vl='0000-00-00 '.$H.':'.$I.':'.$S;
		
		return $vl;
	}

	public static function getDatetimeFormattedFromCanonical($vl, $parameters=array()){
		$formattypes=self::$formattypes;
		$matches=array();
		preg_match('/(\d+)-(\d+)-(\d+) (\d+):(\d+):(\d+)/', $vl, $matches);

		//print_r($matches);

		$inFormFormat=self::$dateFormat;

		if(!isset($formattypes[$inFormFormat])){
			$inFormFormat='standart';
		}

			
		$valueFormat=(isset($parameters['format']) ? $parameters['format'] : '');
		if($valueFormat==''){
			$valueFormat='DT';
		}

		if($valueFormat=='T' || $valueFormat=='DT'){
			if(isset($parameters['noSeconds']) && $parameters['noSeconds']==1){
				$valueTime=$matches[4].':'.$matches[5];
			}else{
				$valueTime=$matches[4].':'.$matches[5].':'.$matches[6];
			}
		}
		
		switch($inFormFormat){
			case 'standart' : {
				$valueDate=$matches[1].'-'.$matches[2].'-'.$matches[3];
				break;
			}
			case 'eu' : {
				$valueDate=$matches[3].'/'.$matches[2].'/'.$matches[1];
				break;
			}
			case 'us' : {
				$valueDate=$matches[2].'/'.$matches[3].'/'.$matches[1];
				break;
			}
		}

		return $valueDate.' '.$valueTime;
	}
	
	public static function getDateFormattedFromCanonical($vl, $parameters=array()){
		$formattypes=self::$formattypes;
		$matches=array();
		
		if($vl!='' && preg_match('/(\d+)-(\d+)-(\d+) (\d+):(\d+):(\d+)/', $vl, $matches)){
			$inFormFormat=self::$dateFormat;
			if(!isset($formattypes[$inFormFormat])){
				$inFormFormat='standart';
			}
			
			switch($inFormFormat){
				case 'standart' : {
					$valueDate=$matches[1].'-'.$matches[2].'-'.$matches[3];
					break;
				}
				case 'eu' : {
					$valueDate=$matches[3].'/'.$matches[2].'/'.$matches[1];
					break;
				}
				case 'us' : {
					$valueDate=$matches[2].'/'.$matches[3].'/'.$matches[1];
					break;
				}
			}
		}else{
			$valueDate='';
		}
		return $valueDate;
	}
	
	public static function getTimeFormattedFromCanonical($vl, $parameters=array()){
		$matches=array();
		if($vl!='' && preg_match('/(\d+)-(\d+)-(\d+) (\d+):(\d+):(\d+)/', $vl, $matches)){
			if($parameters['noSeconds']==1){
				$valueTime=$matches[4].':'.$matches[5];
			}else{
				$valueTime=$matches[4].':'.$matches[5].':'.$matches[6];
			}
		}else{
			$valueTime='';
		}
		return $valueTime;
	}
}