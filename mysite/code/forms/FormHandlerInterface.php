<?php

interface FormHandlerInterface {

	public function __construct($data);

	public function process();
}