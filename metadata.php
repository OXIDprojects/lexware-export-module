<?php
/**
 * This file is part of OXID eSales Lexware export module.
 *
 * OXID eSales Lexware export module is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * OXID eSales Lexware export module is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OXID eSales Lexware export module.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @category      module
 * @package       lexwareexport
 * @author        OXID eSales AG
 * @link          http://www.oxid-esales.com
 * @copyright (C) OXID eSales AG 2003-2015
 */

/**
 * Metadata version
 */
$sMetadataVersion = '1.1';

/**
 * Module information
 */
$aModule = array(
    'id'          => 'oelexwareexport',
    'title'       => array(
        'de' => 'OE Lexware Export',
        'en' => 'OE Lexware Export',
    ),
    'description' => array(
        'de' => 'Dieses Modul exportiert Bestellinformationen im XML-Format',
        'en' => 'This module exports order information into an XML format',
    ),
    'thumbnail'   => 'out/pictures/picture.png',
    'version'     => '1.0.0',
    'author'      => 'OXID eSales AG',
    'url'         => 'http://www.oxid-esales.com',
    'extend'      => array(
        'order_overview' => 'oe/lexwareexport/controllers/admin/oelexwareexportorder_overview',
    ),
    'files'       => array(
        'OeLexwareExportImex' => 'oe/lexwareexport/models/oelexwareexportimex.php',
    ),
    'templates'   => array(
    ),
    'blocks'      => array(
        array('template' => 'order_overview.tpl', 'block'=>'admin_order_overview_export', 'file'=>'/views/blocks/order_export.tpl'),
    ),
    'settings'    => array(
        array('group' => 'main', 'name' => 'aOELexwareExportVAT', 'type' => 'aarr', 'value' =>
            array(
                '1' => '19',
                '2' => '7',
                '3' => '16',
                '4' => '20',
                '5' => '10',
            )
        ),
    ),
);
