<?php

/**
 *  ____        _ _    ___  ____    _  _    ___
 * / ___|  __ _| | |_ / _ \/ ___|  | || |  / _ \
 * \___ \ / _` | | __| | | \___ \  | || |_| | | |
 *  ___) | (_| | | |_| |_| |___) | |__   _| |_| |
 * |____/ \__,_|_|\__|\___/|____/     |_|(_)___/
 *
 * SaltOS: Framework to develop Rich Internet Applications
 * Copyright (C) 2007-2025 by Josep Sanz Campderrós
 * More information in https://www.saltos.org or info@saltos.org
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

// phpcs:disable PSR1.Classes.ClassDeclaration
// phpcs:disable Squiz.Classes.ValidClassName
// phpcs:disable PSR1.Methods.CamelCapsMethodName
// phpcs:disable PSR1.Files.SideEffects
// phpcs:disable Generic.Files.LineLength

/**
 * Test web customers
 *
 * This test performs all actions of the customers app suck as: create, insert,
 * list, view, edit, update and delete, using the apache sapi interface
 */

/**
 * Importing namespaces
 */
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\Depends;

/**
 * Loading helper function
 *
 * This file contains the needed function used by the unit tests
 */
require_once 'lib/utestlib.php';

/**
 * Main class of this unit test
 */
final class test_web_customers extends TestCase
{
    #[testdox('authtoken action')]
    /**
     * Authtoken
     *
     * This function execute the authtoken rest request, and must to get the
     * json with the valid token to continue in the nexts unit tests
     */
    public function test_authtoken(): array
    {
        $json = test_web_helper('auth/login', [
            'user' => 'admin',
            'pass' => 'admin',
        ], '', '');
        $this->assertSame($json['status'], 'ok');
        $this->assertSame(count($json), 4);
        $this->assertArrayHasKey('token', $json);
        return $json;
    }

    #[Depends('test_authtoken')]
    #[testdox('create action')]
    /**
     * Create
     *
     * This function execute the creates rest request, and must to get the
     * json with the layout without data
     */
    public function test_create(array $json): array
    {
        $json2 = test_web_helper('app/customers/create', '', $json['token'], '');
        $this->assertArrayHasKey('cache', $json2);
        $this->assertArrayNotHasKey('data', $json2);
        return $json;
    }

    #[Depends('test_create')]
    #[testdox('insert action')]
    /**
     * Insert
     *
     * This function execute the insert rest request, to do it send the json with
     * the data that they want to insert and must to get the json with the status
     * and the create_id.
     */
    public function test_insert(array $json): array
    {
        $json2 = test_web_helper('app/customers/insert', [], '', '');
        $this->assertArrayHasKey('error', $json2);

        $json2 = test_web_helper('app/dashboard/insert', [], $json['token'], '');
        $this->assertArrayHasKey('error', $json2);

        $json2 = test_web_helper('app/customers/insert', [], $json['token'], '');
        $this->assertArrayHasKey('status', $json2);
        $this->assertSame($json2['status'], 'ko');

        $json2 = test_web_helper('app/customers/insert', [
            'nada' => 'nada',
        ], $json['token'], '');
        $this->assertArrayHasKey('status', $json2);
        $this->assertSame($json2['status'], 'ko');

        $json2 = test_web_helper('app/customers/insert', [
            'name' => 'The SaltOS project',
            'code' => '12345678X',
            'city' => 'Barcelona',
            'zip' => '08001',
        ], $json['token'], '');
        $this->assertSame($json2['status'], 'ok');
        $this->assertSame(count($json2), 2);
        $this->assertArrayHasKey('created_id', $json2);

        return [
            'token' => $json['token'],
            'created_id' => $json2['created_id'],
        ];
    }

    #[Depends('test_insert')]
    #[testdox('list action')]
    /**
     * List
     *
     * This function execute the list rest request, to do it send the json with
     * the search that they want to use in the list filter and receives the json
     * with the data used to populate the table.
     */
    public function test_list(array $json): array
    {
        $json2 = test_web_helper('app', [], '', '');
        $this->assertArrayHasKey('error', $json2);

        $json2 = test_web_helper('app/nada', [], '', '');
        $this->assertArrayHasKey('error', $json2);

        $json2 = test_web_helper('app/customers/nada', [], '', '');
        $this->assertArrayHasKey('error', $json2);

        $json2 = test_web_helper('app/customers', [], '', '');
        $this->assertArrayHasKey('error', $json2);

        $json2 = test_web_helper('app/customers/list/data', [
            'page' => 1,
        ], $json['token'], '');
        $this->assertArrayHasKey('data', $json2);

        $search = 'The SaltOS project 12345678X';
        $json2 = test_web_helper('app/customers/list/data', [
            'search' => $search,
        ], $json['token'], '');
        $this->assertTrue(count($json2['data']) == 1);

        //~ $json2 = test_web_helper('app/customers/widget/table1', '', $json['token'], '');
        //~ $this->assertArrayHasKey('data', $json2);

        //~ $json2 = test_web_helper('app/customers/widget/table2', '', $json['token'], '');
        //~ $this->assertArrayHasKey('data', $json2);

        $json2 = test_web_helper('app/customers/list/data', [
            'search' => '',
        ], $json['token'], '');
        $this->assertArrayHasKey('data', $json2);

        return [
            'token' => $json['token'],
            'created_id' => $json['created_id'],
        ];
    }

    #[Depends('test_list')]
    #[testdox('view action')]
    /**
     * View
     *
     * This function execute the view rest request, intended to retrieve the detail
     * of the app with the layout needed to render it.
     */
    public function test_view(array $json): array
    {
        $id = $json['created_id'];

        $json2 = test_web_helper("app/customers/view/$id", '', $json['token'], '');
        $this->assertArrayHasKey('cache', $json2);
        $this->assertArrayHasKey('data', $json2);

        return [
            'token' => $json['token'],
            'created_id' => $json['created_id'],
        ];
    }

    #[Depends('test_view')]
    #[testdox('edit action')]
    /**
     * Edit
     *
     * This function execute the view rest request, intended to retrieve the detail
     * of the app with the layout needed to render it.
     */
    public function test_edit(array $json): array
    {
        $id = $json['created_id'];

        $json2 = test_web_helper("app/customers/edit/$id", '', $json['token'], '');
        $this->assertArrayHasKey('cache', $json2);
        $this->assertArrayHasKey('data', $json2);

        return [
            'token' => $json['token'],
            'created_id' => $json['created_id'],
        ];
    }

    #[Depends('test_edit')]
    #[testdox('upgrade action')]
    /**
     * Upgrade
     *
     * This function execute the update rest request, to do it send the json with
     * the data that they want to update and must to get the json with the status
     * and the updated_id.
     */
    public function test_update(array $json): array
    {
        $id = $json['created_id'];

        $json2 = test_web_helper('app/customers/update', [], '', '');
        $this->assertArrayHasKey('error', $json2);

        $json2 = test_web_helper('app/dashboard/update', [], $json['token'], '');
        $this->assertArrayHasKey('error', $json2);

        $json2 = test_web_helper("app/customers/update/$id", [], $json['token'], '');
        $this->assertArrayHasKey('status', $json2);
        $this->assertSame($json2['status'], 'ko');

        $json2 = test_web_helper("app/customers/update/$id", [
            'nada' => 'nada',
        ], $json['token'], '');
        $this->assertArrayHasKey('status', $json2);
        $this->assertSame($json2['status'], 'ko');

        $json2 = test_web_helper("app/customers/update/$id", [
            'name' => 'The SaltOS project v2',
            'code' => '12345678Z',
        ], $json['token'], '');
        $this->assertSame($json2['status'], 'ok');
        $this->assertSame(count($json2), 2);
        $this->assertArrayHasKey('updated_id', $json2);

        return [
            'token' => $json['token'],
            'updated_id' => $json2['updated_id'],
        ];
    }

    #[Depends('test_update')]
    #[testdox('delete action')]
    /**
     * Delete
     *
     * This function execute the delete rest request, they must to get the json
     * with the status and the deleted_id.
     */
    public function test_delete(array $json): void
    {
        $id = $json['updated_id'];

        $json2 = test_web_helper('app/customers/delete', [], '', '');
        $this->assertArrayHasKey('error', $json2);

        $json2 = test_web_helper('app/dashboard/delete', [], $json['token'], '');
        $this->assertArrayHasKey('error', $json2);

        $json2 = test_web_helper("app/customers/delete/$id", '', $json['token'], '');
        $this->assertSame($json2['status'], 'ok');
        $this->assertSame(count($json2), 2);
        $this->assertArrayHasKey('deleted_id', $json2);
    }
}
