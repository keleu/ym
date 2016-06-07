<?php
/**
* Encode and decode geohashes
*/
class  Geohash
{
	// Base32位 编码
	protected static $coding="0123456789bcdefghjkmnpqrstuvwxyz";
	protected static $codingMap=array('0'=>'00000','1'=>'00001','2'=>'00010','3'=>'00011','4'=>'00100','5'=>'00101','6'=>'00110','7'=>'00111','8'=>'01000','9'=>'01001','b'=>'01010','c'=>'01011','d'=>'01100','e'=>'01101','f'=>'01110','g'=>'01111','h'=>'10000','j'=>'10001','k'=>'10010','m'=>'10011','n'=>'10100','p'=>'10101','q'=>'10110','r'=>'10111','s'=>'11000','t'=>'11001','u'=>'11010','v'=>'11011','w'=>'11100','x'=>'11101','y'=>'11110','z'=>'11111');
	protected static $neighbors= array(array('p0r21436x8zb9dcf5h7kjnmqesgutwvy',
											'bc01fg45238967deuvhjyznpkmstqrwx',
										    '14365h7k9dcfesgujnmqp0r2twvyx8zb',
										    '238967debc01fg45kmstqrwxuvhjyznp')
										,array("bc01fg45238967deuvhjyznpkmstqrwx","p0r21436x8zb9dcf5h7kjnmqesgutwvy","238967debc01fg45kmstqrwxuvhjyznp","14365h7k9dcfesgujnmqp0r2twvyx8zb"));
	protected static $borders = array(array("prxz", "bcfguvyz", "028b", "0145hjnp"),
		 							  array("bcfguvyz", "prxz", "0145hjnp", "028b"));
	protected static $deep_map = array(

								);


    public function __construct(){
    	// 精度(long, lat)单位m => 编码个数
        // '0.04,0.02'=>'12',
		// '0.15,0.15'=>'11',
		// '1.2,0.6'=>'10',
		// '4.8,4.8'=>'9',
		// '40,20'=>'8',
		// '150,150'=>'7',
		// '1200,609'=>'6',
		// '4900, 4900'=>'5',
		// '40000,20000'=>'4'
    }

	/**
	 * 将geohash值从Base32解码为经纬度
	 * Time：2016/01/06 18:33:36
	 * @author zhuli
	 * @param 参数类型 string
	 * @return 返回值类型 array
	*/
	public static function decode($hash)
	{
		$binary="";
		$hl=strlen($hash);
		for($i=0; $i<$hl; $i++)
		{
			$binary.=self::$codingMap[substr($hash,$i,1)];
		}

		$bl=strlen($binary);
		$blat="";
		$blong="";
		for ($i=0; $i<$bl; $i++)
		{
			if ($i%2)
				$blat=$blat.substr($binary,$i,1);
			else
				$blong=$blong.substr($binary,$i,1);

		}

		$lat=self::binDecode($blat,-90,90);
		$long=self::binDecode($blong,-180,180);

		$latErr=self::calcError(strlen($blat),-90,90);
		$longErr=self::calcError(strlen($blong),-180,180);

		$latPlaces=max(1, -round(log10($latErr))) - 1;
		$longPlaces=max(1, -round(log10($longErr))) - 1;

		//round it
		$lat=round($lat, $latPlaces);
		$long=round($long, $longPlaces);

		return array( 'longitude' =>$long,'latitude'=>$lat);
	}


	/**
	 * 从经纬度转码为获得Base32的geohash值
	 * Time：2016/01/06 18:33:36
	 * @author zhuli
	 * @param 参数类型
	 *        decimal  $long 经度
	 *        decimal  $lat  纬度
	 * @return 返回值类型 array
	*/
	public static function encode($long,$lat)
	{
		//how many bits does latitude need?
		$plat= self::precision($lat);
		$latbits=1;
		$err=45;
		while($err>$plat)
		{
			$latbits++;
			$err/=2;
		}

		//how many bits does longitude need?
		$plong=self::precision($long);
		$longbits=1;
		$err=90;
		while($err>$plong)
		{
			$longbits++;
			$err/=2;
		}

		//bit counts need to be equal
		$bits=max($latbits,$longbits);

		//as the hash create bits in groups of 5, lets not
		//waste any bits - lets bulk it up to a multiple of 5
		//and favour the longitude for any odd bits
		$longbits=$bits;
		$latbits=$bits;
		$addlong=1;
		while (($longbits+$latbits)%5 != 0)
		{
			$longbits+=$addlong;
			$latbits+=!$addlong;
			$addlong=!$addlong;
		}


		//encode each as binary string
		$blat=self::binEncode($lat,-90,90, $latbits);
		$blong=self::binEncode($long,-180,180,$longbits);

		//merge lat and long together
		$binary="";
		$uselong=1;
		while (strlen($blat)+strlen($blong))
		{
			if ($uselong)
			{
				$binary=$binary.substr($blong,0,1);
				$blong=substr($blong,1);
			}
			else
			{
				$binary=$binary.substr($blat,0,1);
				$blat=substr($blat,1);
			}
			$uselong=!$uselong;
		}

		//convert binary string to hash
		$hash="";
		for ($i=0; $i<strlen($binary); $i+=5)
		{
			$n=bindec(substr($binary,$i,5));
			$hash=$hash.self::$coding[$n];
		}

		return $hash;
	}

	// 获得相邻的所有网格的hash值
	public static function get_neighbor($hash, $direction){
		$hash_length = strlen($hash);
        // 最后一个字符
	    $last_char = substr($hash, $hash_length-1);

	    $type = $hash_length % 2;

	    $border = self::$borders[$type];
	    $neighbor = self::$neighbors[$type];
	    $base .= substr($hash, 0, $hash_length - 1);

        // 若边界中不存在
	    if(strpos($border[$direction], $last_char) === false){
	    }else{
	        $base = self::get_neighbor($base, $direction);
	    }
		$neighbor_index = strpos($neighbor[$direction], $last_char);

	    $base .= self::$coding[$neighbor_index];
	    return $base;
	}

	public static function geohash_neighbors($hash){
		$NORTH = 0;
		$EAST = 1;
		$SOUTH = 2;
		$WEST = 3;

	    $neighbors = array();

	    if($hash) {
	        // N, NE, E, SE, S, SW, W, NW
	        $neighbors[0] = self::get_neighbor($hash, $NORTH);
	        $neighbors[1] = self::get_neighbor($neighbors[0], $EAST);
	        $neighbors[2] = self::get_neighbor($hash, $EAST);
	        $neighbors[3] = self::get_neighbor($neighbors[2], $SOUTH);
	        $neighbors[4] = self::get_neighbor($hash, $SOUTH);
	        $neighbors[5] = self::get_neighbor($neighbors[4], $WEST);
	        $neighbors[6] = self::get_neighbor($hash, $WEST);
	        $neighbors[7] = self::get_neighbor($neighbors[6], $NORTH);
	    }
	    return $neighbors;
	}

	/**
	* What's the maximum error for $bits bits covering a range $min to $max
	*/
	private static function calcError($bits,$min,$max)
	{
		$err=($max-$min)/2;
		while ($bits--)
			$err/=2;
		return $err;
	}

	/*
	* returns precision of number
	* precision of 42 is 0.5
	* precision of 42.4 is 0.05
	* precision of 42.41 is 0.005 etc
	*/
	private static function precision($number)
	{
		$precision=0;
		$pt=strpos($number,'.');
		if ($pt!==false)
		{
			$precision=-(strlen($number)-$pt-1);
		}

		return pow(10,$precision)/2;
	}

	private static function binEncode($number, $min, $max, $bitcount)
	{
		if ($bitcount==0)
			return "";

		$mid=($min+$max)/2;
		if ($number>$mid)
			return "1".self::binEncode($number, $mid, $max,$bitcount-1);
		else
			return "0".self::binEncode($number, $min, $mid,$bitcount-1);
	}

	private function binDecode($binary, $min, $max)
	{
		$mid=($min+$max)/2;

		if (strlen($binary)==0)
			return $mid;

		$bit=substr($binary,0,1);
		$binary=substr($binary,1);

		if ($bit==1)
			return self::binDecode($binary, $mid, $max);
		else
			return self::binDecode($binary, $min, $mid);
	}

	public static function getDistance($lng_p, $lat1_p, $lng_sp, $lat_sp){

		return ;
	}
}