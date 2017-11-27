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
 * Class oeLexwareExportOrder_Overview.
 * Extends Order_Overview.
 *
 * @see Order_Overview
 */
class oeLexwareExportOrder_Overview extends oeLexwareExportOrder_Overview_parent
{
    /**
     * Performs Lexware export to user (outputs file to save).
     */
    public function oeLexwareExportExportLex()
    {
        $request = \OxidEsales\Eshop\Core\Registry::get(\OxidEsales\Eshop\Core\Request::class);
        $sOrderNr = $request->getRequestParameter("ordernr");
        $sToOrderNr = $request->getRequestParameter("toordernr");
        $oImex = oxNew("OeLexwareExportImex");
        if (($sLexware = $oImex->exportLexwareOrders($sOrderNr, $sToOrderNr))) {
            $oUtils = \OxidEsales\Eshop\Core\Registry::getUtils();
            $oUtils->setHeader("Pragma: public");
            $oUtils->setHeader("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            $oUtils->setHeader("Expires: 0");
            $oUtils->setHeader("Content-type: application/x-download");
            $oUtils->setHeader("Content-Length: " . strlen($sLexware));
            $oUtils->setHeader("Content-Disposition: attachment; filename=intern.xml");
            $oUtils->showMessageAndExit($sLexware);
        }
    }
}
