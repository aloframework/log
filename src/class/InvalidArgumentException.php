<?php
    /**
 *    Copyright (c) Arturas Molcanovas <a.molcanovas@gmail.com> 2016.
 *    https://github.com/aloframework/log
 *
 *    Licensed under the Apache License, Version 2.0 (the "License");
 *    you may not use this file except in compliance with the License.
 *    You may obtain a copy of the License at
 *
 *        http://www.apache.org/licenses/LICENSE-2.0
 *
 *    Unless required by applicable law or agreed to in writing, software
 *    distributed under the License is distributed on an "AS IS" BASIS,
 *    WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *    See the License for the specific language governing permissions and
 *    limitations under the License.
 */

    namespace AloFramework\Log;

    /**
     * Invalid argument exception codes
     *
     * @author Art <a.molcanovas@gmail.com>
     */
    class InvalidArgumentException extends \Psr\Log\InvalidArgumentException {

        /**
         * Code for an invalid log save path
         *
         * @var int
         */
        const E_PATH = 101;

        /**
         * Code for an invalid log label
         *
         * @var int
         */
        const E_LABEL = 102;

        /**
         * Code for an invalid log level
         *
         * @var int
         */
        const E_LEVEL = 103;
    }
