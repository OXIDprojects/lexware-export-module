<?php
/**
 * #PHPHEADER_OXID_LICENSE_INFORMATION#
 */

class OeLexwareOrderOverviewTest extends OxidEsales\TestingLibrary\UnitTestCase
{
    /**
     * Order_Overview::Exportlex() test case
     *
     * @return null
     */
    public function testExportlex()
    {
        oxTestModules::addFunction('OeLexwareExportImex', 'exportLexwareOrders', '{ return "testExportData"; }');
        oxTestModules::addFunction('oxUtils', 'setHeader', '{ if ( !isset( $this->_aHeaderData ) ) { $this->_aHeaderData = array();} $this->_aHeaderData[] = $aA[0]; }');
        oxTestModules::addFunction('oxUtils', 'getHeaders', '{ return $this->_aHeaderData; }');
        oxTestModules::addFunction('oxUtils', 'showMessageAndExit', '{ $this->_aHeaderData[] = $aA[0]; }');

        // testing..
        $oView = oxNew(\OxidEsales\Eshop\Application\Controller\Admin\OrderOverview::class);
        $oView->oeLexwareExportExportLex();

        $aHeaders = \OxidEsales\Eshop\Core\Registry::getUtils()->getHeaders();
        $this->assertEquals("Pragma: public", $aHeaders[0]);
        $this->assertEquals("Cache-Control: must-revalidate, post-check=0, pre-check=0", $aHeaders[1]);
        $this->assertEquals("Expires: 0", $aHeaders[2]);
        $this->assertEquals("Content-type: application/x-download", $aHeaders[3]);
        $this->assertEquals("Content-Length: " . strlen("testExportData"), $aHeaders[4]);
        $this->assertEquals("Content-Disposition: attachment; filename=intern.xml", $aHeaders[5]);
        $this->assertEquals("testExportData", $aHeaders[6]);
    }
}
