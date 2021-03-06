<?php

/**
 * Copyright (c) Enalean, 2016. All Rights Reserved.
 *
 * This file is a part of Tuleap.
 *
 * Tuleap is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Tuleap is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Tuleap. If not, see <http://www.gnu.org/licenses/>.
 */

require_once('bootstrap.php');

use Tuleap\OpenIDConnectClient\Router;

class RouterTest extends TuleapTestCase {

    private $existing_response;

    public function setUp() {
        parent::setUp();
        $this->existing_response = $GLOBALS['Response'];
        $GLOBALS['Response']     = mock('Layout');
    }

    public function tearDown() {
        $GLOBALS['Response'] = $this->existing_response;
        parent::tearDown();
    }

    public function itOnlyAcceptsHTTPS() {
        $login_controller          = mock('Tuleap\OpenIDConnectClient\Login\Controller');
        $account_linker_controller = mock('Tuleap\OpenIDConnectClient\AccountLinker\Controller');
        $user_mapping_controller   = mock('Tuleap\OpenIDConnectClient\UserMapping\Controller');
        $request                   = mock('HTTPRequest');
        $request->setReturnValue('isSecure', false);

        $GLOBALS['Response']->expectOnce('redirect');

        $router = new Router($login_controller, $account_linker_controller, $user_mapping_controller);
        $router->route($request);
    }

}