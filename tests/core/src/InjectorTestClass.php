<?php

    namespace Og;

    /**
     * @package Og
     * @version 0.1.0
     * @author  Greg Truesdell <odd.greg@gmail.com>
     */
    class InjectorTestClass
    {
        /**
         * @var Forge
         */
        private $di;

        /**
         * InjectorTestClass constructor.
         *
         * @param Forge $di
         */
        public function __construct(Forge $di)
        {
            $this->di = $di;
        }

        /**
         * @param Forge   $di
         * @param array   $test_array
         * @param integer $test_value
         */
        public function InjectorTarget(Forge $di, array $test_array, $test_value)
        {

        }

    }
