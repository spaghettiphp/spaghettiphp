<?php 

class Date extends Object{

	/**
	  *	Razões de conversão entre datas
	  */
    private $convert = array(
        'seconds' => 1,
        'minutes' => 60,
        'hours'   => 3600,
        'days'    => 86400,
        'weeks'   => 604800,
        'months'  => 18144000,
        'years'   => 217728000,    
    );
	
	private $holidays = array(
		'01/01','21/04','01/05','07/09',
		'12/10','02/11','20/11','15/11','25/12'
	);
 
	/**
	  *	Formato de retorno da data
	  */
    private $format = 'd/m/Y';
    
	/**
	  *	Retorna uma instância única do objeto
	  *	
	  *	@return object
	  */
	public static function &getInstance() {
        static $instance = array();
        if(!isset($instance[0]) || !$instance[0]):
            $instance[0] = new Date();
        endif;
        return $instance[0];
    }

	/**
	  *	Calcula a diferença entre as datas informadas
	  *	
	  *	@param  datetime $date      A data a ser verificada
	  *	@param  datetime $otherDate A outra data para calcular a diferença. Se não for
	  *		informado, será usado a data atual
	  *	@param  strint   $returnAs  Informa o modo de retorno do cálculo
	  *	@return retorna a quantidade de dias, meses, etc, dependendo de $returnAs
	  */
    public static function diff($date, $otherDate = null, $returnAs = 'days'){
		$self = self::getInstance();
        $otherDate = (empty($otherDate)) ? date("Y-m-d H:i:s") : $otherDate;
        return round(abs((strtotime($date) - strtotime($otherDate)) / $self->convert[$returnAs]));
    }
	
	/**
	  *	Adiciona dias à data e retorna o valor no formato indicado em Date::format
	  *	
	  *	@param  $num     Número que será adicionado
	  *	@param  $date    Data que receberá a adição. Se não for informada, recebe a data atual
	  *	@param  $convert Pode receber hours, minutes, seconds, months, days e years. Quando
	  *		informa 'days' estamos adicionando dias. Se for 'months' serão adicionados meses
	  *		e assim por diante
	  *	@return datetime Retorna no formato datetime()
	  */
    public static function add($num, $date = null, $convert = 'days', $format = null){
		$self = self::getInstance();
		return self::format(self::addInTime($num, $date, $convert), $format);
    }
	
	/**
	  *	Retorna o próximo dia útil
	  *	
	  *	@param  datetime Data
	  *	@return datetime Próximo dia útil
	  */
	public static function nextWorkday($date = null){
		$self = self::getInstance();
        $date = empty($date) ? date('Y-m-d') : $date;
		
		$date = self::add(1, $date, 'days', 'Y-m-d');
		
		if(!self::isWorkday($date)):
			while(self::isWeekend($date) || self::isHoliday($date))
				$date = self::add(1, $date, 'days', 'Y-m-d');
		endif;
		
		return self::format($date);
	}
	
	/**
	  *	Formata uma data como solicitado
	  *	
	  *	@param  datetime Data pode ser no formato suportado pelo strtotime, ou um inteiro
	  *             com o valor do time()
	  *	@param  string   Formato desejado
	  *	@return datetime
	  */
	public static function format($date, $format = null){
		$self = self::getInstance();
		$format = empty($format) ? $self->format : $format;
        $date = is_numeric($date) ? $date : strtotime($date);
		return date($format, $date);
	}
	
	/**
	  *	O dia informado é um sábado ou domingo?
	  *	
	  *	@param  datetime Data
	  *	@return bool True se for fim de semana, False caso não seja
	  */
	public static function isWeekend($date = null){
        $dateTime = empty($date) ? time() : strtotime($date);
		return date('N', $dateTime) < 6 ? false : true;
	}
	
	/**
	  *	O dia informado é um feriado?
	  *	
	  *	@param  datetime Data
	  *	@return bool
	  */
	public static function isHoliday($date = null){
		$self = self::getInstance();
        $dateTime = empty($date) ? time() : strtotime($date);

		return in_array(date('d/m', $dateTime), $self->holidays);
	}
	
	/**
	  *	O dia informado é um dia útil?
	  *	
	  *	@param  datetime Data
	  *	@return bool
	  */
	public static function isWorkday($date = null){
        $date = empty($date) ? date('Y-m-d') : $date;
		return !self::isWeekend($date) && !self::isHoliday($date);
	}
	
	/**
	  *	Adiciona dias à data e retorna o valor em time
	  *	
	  *	@param  $num     Número que será adicionado
	  *	@param  $date    Data que receberá a adição. Se não for informada, recebe a data atual
	  *	@param  $convert Pode receber hours, minutes, seconds, months, days e years. Quando
	  *		informa 'days' estamos adicionando dias. Se for 'months' serão adicionados meses
	  *		e assim por diante
	  *	@return datetime Retorna no formato time()
	  */
	public static function addInTime($num, $date = null, $convert = 'days'){
		$self = self::getInstance();
        $date = empty($date) ? time() : strtotime($date);
		return mktime(
			($convert == 'hours')   ? date('H', $date) + $num : date('H', $date),
			($convert == 'minutes') ? date('i', $date) + $num : date('i', $date),
			($convert == 'seconds') ? date('s', $date) + $num : date('s', $date),
			($convert == 'months')  ? date('m', $date) + $num : date('m', $date),
			($convert == 'days')    ? date('d', $date) + $num : date('d', $date),
			($convert == 'years')   ? date('Y', $date) + $num : date('Y', $date)
		);
	}
	
	/**
	  *	Adiciona um feriado à lista
	  *	
	  *	@param  datetime Data no formato d/m
	  *	@return void
	  */
	public static function addHoliday($date){
		$self = self::getInstance();
		$self->holidays[] = $date;
	}
	
	/**
	  *	Adiciona um feriado à lista
	  *	
	  *	@param  datetime Data no formato d/m
	  *	@return void
	  */
	public static function setFormat($format){
		$self = self::getInstance();
		$self->format = $format;
	}
	
	
}