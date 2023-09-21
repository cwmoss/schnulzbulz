<?php

namespace disco\model;
// use Monolog\Handler\HandlerInterface;
// use Monolog\Handler\FormattableHandlerTrait;
use Monolog\Handler\AbstractHandler;

/*
	this thing is a monolog handler that stops bubbling to the real thing
	until the message "finished" comes in 
	it should be put on top of the handlers stack

	this thing is also a monolog processor that collects all "context" vars
	in the "extra" record field
	collecting is done via message "data"
*/

class collecting_data_handler extends AbstractHandler {
	// use FormattableHandlerTrait;

	public $extra = [];
	public $started;

	public function __construct() {
		$this->started = microtime(true);
		$this->extra['method'] = $_SERVER['REQUEST_METHOD'] ?? 'null';
		parent::__construct();
	}

	/*
		true  == stop bubbling
		false == bubble down the stack
	*/
	public function handle($record) {

		if ($record['message'] != 'finished') {
			return true;
		} else {
			return false;
		}
	}

	/*
	here comes the "processor" part
*/
	public function __invoke($record) {

		if ($record['message'] == 'data') {
			$this->extra = array_merge($this->extra, $record['context']);
		}
		if ($record['message'] == 'finished') {
			$ended = microtime(true);
			$this->extra['action'] = 'TODO'; // Xorcapp::$inst->ctrl_name . '/' . Xorcapp::$inst->act;
			$this->extra['started'] = $this->started;
			$this->extra['finished'] = $ended;
			$this->extra['duration'] = ($ended - $this->started);
			$this->extra['response_code'] = http_response_code();
			$record['extra'] = $this->extra;
		}

		return $record;
	}
}
