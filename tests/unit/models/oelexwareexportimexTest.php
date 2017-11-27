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

use OxidEsales\Eshop\Core\Field;

class OeLexwareExportImexTest extends OxidEsales\TestingLibrary\UnitTestCase
{
    protected $adminUserId;

    protected function setUp()
    {
        $db = \OxidEsales\Eshop\Core\DatabaseProvider::getDb();
        $this->adminUserId = $db->getOne("select OXID from oxuser where OXUSERNAME='admin'");
    }

    /**
     * Tear down the fixture.
     *
     * @return null
     */
    protected function tearDown()
    {
        $this->cleanUpTable('oxorder');
        $this->cleanUpTable('oxorderarticles');
        parent::tearDown();
    }

    public function test_exportLexwareArticles()
    {
        $myConfig = $this->getConfig();
        //$sFile = $myConfig->sShopDir.'/tmp/test.xpr';
        $sFile = \OxidEsales\Eshop\Core\Registry::get("oxConfigFile")->getVar("sCompileDir") . '/test.xpr';
        @unlink($sFile);
        $oImex = new OeLexwareExportImex();
        if (!$oImex->exportLexwareArticles(0, 1000, $sFile)) {
            $this->fail("error exporting lexware");
        }
        $sContents = file_get_contents($sFile);
        @unlink($sFile);
        // we have full list [hopefully] of exported articles
        $aContents = explode("\n", str_replace("\r", '', $sContents));
        // check header
        $this->assertEquals(
            '"Artikelnummer";"Bezeichnung";"Einheit";"Gewicht";"Matchcode";"Preis pro Anzahl";'
            . '"Warengruppe";"Warengr.-Kurzbez.";"Warengr.-Steuersatz";"Warengr.-Konto Inland";'
            . '"Warengr.-Konto Ausland";"Warengr.-Konto EG";"Preis 1";"Preis 2";"Preis 3";'
            . '"Preis I/1";"Preis I/2";"Preis I/3";"Preis II/1";"Preis II/2";"Preis II/3";"Preis III/1";'
            . '"Preis III/2";"Preis III/3";"B/N";"Lagerartikel";"EK 1";"Währung EK1";"EK 2";'
            . '"Währung EK2";"Staffelmenge 1";"Staffelmenge 2";"Staffelmenge 3";"Lieferantennummer 1";'
            . '"Lieferantennummer 2";"Bestellmenge Lf.1";"Bestellmenge Lf.2";"Bestellnr. Lf.1";'
            . '"Bestellnr. Lf.2";"Lieferzeit Lf.1";"Lieferzeit Lf.2";"Lagerbestand";"Mindestbestand";'
            . '"Lagerort";"Bestellte Menge";"Stückliste";"Internet";"Text"', $aContents[0]
        );
        $blFound = false;
        foreach ($aContents as $content) {
            if (strpos($content, '2000;Wanduhr ROBOT') === 0) {
                $this->assertEquals(
                    '2000;Wanduhr ROBOT ;Stueck;0;2000;1,000;;;;;;;29.00;;;;;;;;;;;;;;0.00;;;;;;;;;'
                    . ';;;;;;2;;;;;1; Wanduhr im coolen ROBOTER Look! Durchmesser: 40 cm Material: Glas '
                    . 'Bezugshinweis: bei Interesse können Sie dieses Produkt bei www.desaster.com erwerben.;', $content
                );
                $blFound = true;
                break;
            }
        }
        $this->assertEquals(true, $blFound);
    }

    public function testInterFormSimple()
    {
        $oImex = new OeLexwareExportImex();
        $this->assertEquals("abra!@#$%^&*()_\"\"cadabra'  \t", $oImex->InterFormSimple("abra!@#$%^&*()_\"cadabra'\r\n \t"));
    }

    public function testInterForm()
    {
        $oImex = new OeLexwareExportImex();
        $this->assertEquals("abra!@#ü &amp; $%^*''_'cadabra' ", $oImex->InterForm("abra<br />!@#&uuml; & $%^*()_\"cadabra'\r\n "));
        $this->assertEquals("abra&amp;cadabra", $oImex->InterForm("abra&cadabra"));
        $o = new stdClass;
        $o->fldtype = "text";
        $this->assertEquals("abra<br />!@#ü &amp; $%^*''_'cadabra' \t", $oImex->InterForm("abra<br />!@#&uuml; & $%^*()_\"cadabra'\r\n \t", $o));
    }

    public function testInternPrice()
    {
        $oImex = new OeLexwareExportImex();
        $this->assertEquals("5.00", $oImex->InternPrice("5,5"));
        $this->assertEquals("1.23", $oImex->InternPrice("1.233"));
        $this->assertEquals("1.21", $oImex->InternPrice("1.205"));
        $this->assertEquals("0.00", $oImex->InternPrice("zxc"));
    }

    public function testExportLexwareOrdersEmptyOrderList()
    {
        $oImex = new OeLexwareExportImex();
        $this->assertNull($oImex->exportLexwareOrders(9991, 9991));

    }

    public function testExportLexwareOrders()
    {
        $myConfig = $this->getConfig();

        $oOrder = oxNew(\OxidEsales\Eshop\Application\Model\Order::class);
        $oOrder->setId('_testOrder');
        $oOrder->oxorder__oxshopid = new Field($myConfig->getBaseShopId());
        $oOrder->oxorder__oxuserid = new Field($this->adminUserId);
        $oOrder->oxorder__oxorderdate = new Field('2007-02-21 00:00:00');
        $oOrder->oxorder__oxordernr = new Field('9991');
        $oOrder->oxorder__oxbillnr = new Field('15');
        $oOrder->oxorder__oxbillcompany = new Field('billcomp');
        $oOrder->oxorder__oxbillemail = new Field('billemail');
        $oOrder->oxorder__oxbillfname = new Field('billfname');
        $oOrder->oxorder__oxbilllname = new Field('billlname');
        $oOrder->oxorder__oxbillstreet = new Field('billstreet');
        $oOrder->oxorder__oxbillstreetnr = new Field('billstnr');
        $oOrder->oxorder__oxbilladdinfo = new Field('billaddinfo');
        $oOrder->oxorder__oxbillustid = new Field('billustid');
        $oOrder->oxorder__oxbillcity = new Field('billcity');
        $oOrder->oxorder__oxbillcountryid = new Field('a7c40f631fc920687.20179984');
        $oOrder->oxorder__oxbillzip = new Field('billzip');
        $oOrder->oxorder__oxbillfon = new Field('billfon');
        $oOrder->oxorder__oxbillfax = new Field('billfax');
        $oOrder->oxorder__oxbillsal = new Field('MR');
        $oOrder->oxorder__oxpaymentid = new Field('oxempty');
        $oOrder->oxorder__oxdelcost = new Field('1');
        $oOrder->oxorder__oxdelvat = new Field('2');
        $oOrder->oxorder__oxpaycost = new Field('3');
        $oOrder->oxorder__oxpayvat = new Field('4');
        $oOrder->oxorder__oxwrapcost = new Field('5');
        $oOrder->oxorder__oxwrapvat = new Field('6');

        $oOrder->oxorder__oxdelcompany = new Field('delcomp');
        $oOrder->oxorder__oxdelfname = new Field('delfname');
        $oOrder->oxorder__oxdellname = new Field('dellname');
        $oOrder->oxorder__oxdelstreet = new Field('delstreet');
        $oOrder->oxorder__oxdelstreetnr = new Field('delstnr');
        $oOrder->oxorder__oxdelzip = new Field('delzip');
        $oOrder->oxorder__oxdelcity = new Field('delcity');
        $oOrder->oxorder__oxdelcountry = new Field('a7c40f631fc920687.20179984');

        $oOrder->save();

        // one test order article
        $oOrderArt = oxNew(\OxidEsales\Eshop\Application\Model\OrderArticle::class);
        $oOrderArt->setId('_testOrderArticle');
        $oOrderArt->oxorderarticles__oxorderid = new Field('_testOrder');
        $oOrderArt->oxorderarticles__oxvat = new Field(19);
        $oOrderArt->oxorderarticles__oxartnum = new Field('1126');
        $oOrderArt->oxorderarticles__oxamount = new Field(1);
        $oOrderArt->oxorderarticles__oxtitle = new Field('Bar-Set ABSINTH');
        $oOrderArt->oxorderarticles__oxselvariant = new Field('oxselvariant');
        $oOrderArt->oxorderarticles__oxnetprice = new Field(28.57);
        $oOrderArt->oxorderarticles__oxbrutprice = new Field(34);
        $oOrderArt->save();

        $myConfig = $this->getConfig();

        $oImex = new OeLexwareExportImex();
        $sResult = str_replace(array("\r", "   "), '', $oImex->exportLexwareOrders(9991, 9991));
        $this->assertEquals(
            "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<Bestellliste>\n<Bestellung zurückgestellt=\"Nein\" bearbeitet=\"Nein\" übertragen=\"Nein\">\n"
            . "<Bestellnummer>9991</Bestellnummer>\n<Rechnungsnummer>15</Rechnungsnummer>\n<Standardwaehrung>978</Standardwaehrung>\n<Bestelldatum>\n<Datum>21.02.2007</Datum>\n<Zeit>00:00:00</Zeit>\n</Bestelldatum>\n<Kunde>\n<Kundennummer></Kundennummer>\n"
            . "<Firmenname>billcomp</Firmenname>\n<Anrede>Herr</Anrede>\n<Vorname>billfname</Vorname>\n<Name>billlname</Name>\n<Strasse>billstreet billstnr</Strasse>\n"
            . "<PLZ>billzip</PLZ>\n<Ort>billcity</Ort>\n<Bundesland></Bundesland>\n<Land>Deutschland</Land>\n<Email>billemail</Email>\n<Telefon>billfon</Telefon>\n<Telefon2></Telefon2>\n"
            . "<Fax>billfax</Fax>\n<Lieferadresse>\n<Firmenname>delcomp</Firmenname>\n<Vorname>delfname</Vorname>\n<Name>dellname</Name>\n<Strasse>delstreet delstnr</Strasse>\n<PLZ>delzip</PLZ>\n<Ort>delcity</Ort>\n<Bundesland></Bundesland>\n"
            . "<Land></Land>\n</Lieferadresse>\n<Matchcode>billlname, billfname</Matchcode>\n<fSteuerbar>ja</fSteuerbar>\n</Kunde>\n<Artikelliste>\n<Artikel>\n<Artikelzusatzinfo><Nettostaffelpreis></Nettostaffelpreis></Artikelzusatzinfo>\n"
            . "<SteuersatzID>1</SteuersatzID>\n<Steuersatz>0.19</Steuersatz>\n<Artikelnummer>1126</Artikelnummer>\n<Anzahl>1</Anzahl>\n<Produktname>Bar-Set ABSINTH/oxselvariant</Produktname>\n"
            . "<Rabatt>0.00</Rabatt>\n<Preis>34.00</Preis>\n</Artikel>\n<GesamtRabatt>0.00</GesamtRabatt>\n<GesamtNetto>28.57</GesamtNetto>\n"
            . "<Lieferkosten>1.00</Lieferkosten>\n<Zahlungsartkosten>3.00</Zahlungsartkosten>\n<GesamtBrutto>34.00</GesamtBrutto>\n<Bemerkung></Bemerkung>\n</Artikelliste>\n<Zahlung>\n<Art></Art>\n</Zahlung>\n</Bestellung>\n</Bestellliste>\n", $sResult
        );
    }

    public function testExportLexwareOrdersDiffCurrency()
    {
        $myConfig = $this->getConfig();

        $oOrder = oxNew(\OxidEsales\Eshop\Application\Model\Order::class);
        $oOrder->setId('_testOrder');
        $oOrder->oxorder__oxshopid = new Field($myConfig->getBaseShopId());
        $oOrder->oxorder__oxuserid = new Field($this->adminUserId);
        $oOrder->oxorder__oxorderdate = new Field('2007-02-21 00:00:00');
        $oOrder->oxorder__oxordernr = new Field('9991');
        $oOrder->oxorder__oxbillnr = new Field('15');
        $oOrder->oxorder__oxbillcompany = new Field('billcomp');
        $oOrder->oxorder__oxbillemail = new Field('billemail');
        $oOrder->oxorder__oxbillfname = new Field('billfname');
        $oOrder->oxorder__oxbilllname = new Field('billlname');
        $oOrder->oxorder__oxbillstreet = new Field('billstreet');
        $oOrder->oxorder__oxbillstreetnr = new Field('billstnr');
        $oOrder->oxorder__oxbilladdinfo = new Field('billaddinfo');
        $oOrder->oxorder__oxbillustid = new Field('billustid');
        $oOrder->oxorder__oxbillcity = new Field('billcity');
        $oOrder->oxorder__oxbillcountryid = new Field('a7c40f631fc920687.20179984');
        $oOrder->oxorder__oxbillzip = new Field('billzip');
        $oOrder->oxorder__oxbillfon = new Field('billfon');
        $oOrder->oxorder__oxbillfax = new Field('billfax');
        $oOrder->oxorder__oxbillsal = new Field('MR');
        $oOrder->oxorder__oxpaymentid = new Field('oxempty');
        $oOrder->oxorder__oxdelcost = new Field('1');
        $oOrder->oxorder__oxdelvat = new Field('2');
        $oOrder->oxorder__oxpaycost = new Field('3');
        $oOrder->oxorder__oxpayvat = new Field('4');
        $oOrder->oxorder__oxwrapcost = new Field('5');
        $oOrder->oxorder__oxwrapvat = new Field('6');

        $oOrder->oxorder__oxdelcompany = new Field('delcomp');
        $oOrder->oxorder__oxdelfname = new Field('delfname');
        $oOrder->oxorder__oxdellname = new Field('dellname');
        $oOrder->oxorder__oxdelstreet = new Field('delstreet');
        $oOrder->oxorder__oxdelstreetnr = new Field('delstnr');
        $oOrder->oxorder__oxdelzip = new Field('delzip');
        $oOrder->oxorder__oxdelcity = new Field('delcity');
        $oOrder->oxorder__oxdelcountry = new Field('a7c40f631fc920687.20179984');

        $oOrder->oxorder__oxcurrate = new Field(2.15);

        $oOrder->save();

        // one test order article
        $oOrderArt = oxNew(\OxidEsales\Eshop\Application\Model\OrderArticle::class);
        $oOrderArt->setId('_testOrderArticle');
        $oOrderArt->oxorderarticles__oxorderid = new Field('_testOrder');
        $oOrderArt->oxorderarticles__oxvat = new Field(19);
        $oOrderArt->oxorderarticles__oxartnum = new Field('1126');
        $oOrderArt->oxorderarticles__oxamount = new Field(1);
        $oOrderArt->oxorderarticles__oxtitle = new Field('Bar-Set ABSINTH');
        $oOrderArt->oxorderarticles__oxselvariant = new Field('oxselvariant');
        $oOrderArt->oxorderarticles__oxnetprice = new Field(28.57);
        $oOrderArt->oxorderarticles__oxbrutprice = new Field(34);
        $oOrderArt->save();

        $myConfig = $this->getConfig();

        $oImex = new OeLexwareExportImex();
        $sResult = str_replace(array("\r", "   "), '', $oImex->exportLexwareOrders(9991, 9991));
        $this->assertEquals(
            "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<Bestellliste>\n<Bestellung zurückgestellt=\"Nein\" bearbeitet=\"Nein\" übertragen=\"Nein\">\n"
            . "<Bestellnummer>9991</Bestellnummer>\n<Rechnungsnummer>15</Rechnungsnummer>\n<Standardwaehrung>978</Standardwaehrung>\n<Bestelldatum>\n<Datum>21.02.2007</Datum>\n<Zeit>00:00:00</Zeit>\n</Bestelldatum>\n<Kunde>\n<Kundennummer></Kundennummer>\n"
            . "<Firmenname>billcomp</Firmenname>\n<Anrede>Herr</Anrede>\n<Vorname>billfname</Vorname>\n<Name>billlname</Name>\n<Strasse>billstreet billstnr</Strasse>\n"
            . "<PLZ>billzip</PLZ>\n<Ort>billcity</Ort>\n<Bundesland></Bundesland>\n<Land>Deutschland</Land>\n<Email>billemail</Email>\n<Telefon>billfon</Telefon>\n<Telefon2></Telefon2>\n"
            . "<Fax>billfax</Fax>\n<Lieferadresse>\n<Firmenname>delcomp</Firmenname>\n<Vorname>delfname</Vorname>\n<Name>dellname</Name>\n<Strasse>delstreet delstnr</Strasse>\n<PLZ>delzip</PLZ>\n<Ort>delcity</Ort>\n<Bundesland></Bundesland>\n"
            . "<Land></Land>\n</Lieferadresse>\n<Matchcode>billlname, billfname</Matchcode>\n<fSteuerbar>ja</fSteuerbar>\n</Kunde>\n<Artikelliste>\n<Artikel>\n<Artikelzusatzinfo><Nettostaffelpreis></Nettostaffelpreis></Artikelzusatzinfo>\n"
            . "<SteuersatzID>1</SteuersatzID>\n<Steuersatz>0.19</Steuersatz>\n<Artikelnummer>1126</Artikelnummer>\n<Anzahl>1</Anzahl>\n<Produktname>Bar-Set ABSINTH/oxselvariant</Produktname>\n"
            . "<Rabatt>0.00</Rabatt>\n<Preis>15.81</Preis>\n</Artikel>\n<GesamtRabatt>0.00</GesamtRabatt>\n<GesamtNetto>13.29</GesamtNetto>\n"
            . "<Lieferkosten>0.47</Lieferkosten>\n<Zahlungsartkosten>1.40</Zahlungsartkosten>\n<GesamtBrutto>15.81</GesamtBrutto>\n<Bemerkung></Bemerkung>\n</Artikelliste>\n<Zahlung>\n<Art></Art>\n</Zahlung>\n</Bestellung>\n</Bestellliste>\n", $sResult
        );
    }

    public function testExportLexwareOrders_setsCorrectCharset()
    {
        $myConfig = $this->getConfig();

        $oOrder = oxNew(\OxidEsales\Eshop\Application\Model\Order::class);
        $oOrder->setId('_testOrder');
        $oOrder->oxorder__oxshopid = new Field($myConfig->getBaseShopId());
        $oOrder->oxorder__oxuserid = new Field($this->adminUserId);
        $oOrder->oxorder__oxorderdate = new Field('2007-02-21 00:00:00');
        $oOrder->oxorder__oxordernr = new Field('9991');
        $oOrder->save();

        $oImex = $this->getMock('OeLexwareExportImex', array('_getCharset'));
        $oImex->expects($this->any())->method('_getCharset')->will($this->returnValue('UTF-8'));

        $sResult = $oImex->exportLexwareOrders(9991, 9991);
        $this->assertTrue((strpos($sResult, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>")) === 0);
    }

    /*
     * Testing if shop is in utf-8 mode, generated xml attributes with special chars
     * are converted to utf-8
     */
    public function testExportLexwareOrders_convertsAttributesSpecChars()
    {
        $myConfig = $this->getConfig();

        $oOrder = oxNew(\OxidEsales\Eshop\Application\Model\Order::class);
        $oOrder->setId('_testOrder');
        $oOrder->oxorder__oxshopid = new Field($myConfig->getBaseShopId());
        $oOrder->oxorder__oxuserid = new Field($this->adminUserId);
        $oOrder->oxorder__oxorderdate = new Field('2007-02-21 00:00:00');
        $oOrder->oxorder__oxordernr = new Field('9991');
        $oOrder->save();

        $oImex = $this->getMock('OeLexwareExportImex', array('_getCharset', '_convertStr'));
        $oImex->expects($this->any())->method('_getCharset')->will($this->returnValue('UTF-8'));
        $oImex->expects($this->at(1))->method('_convertStr')->with($this->equalTo("zurückgestellt"));
        $oImex->expects($this->at(2))->method('_convertStr')->with($this->equalTo("übertragen"));

        $sResult = $oImex->exportLexwareOrders(9991, 9991);
    }

    /*
     * Test converting string from ISO-8859-15 to selected charset
     */
    public function testConvertStr()
    {
        $oImex = $this->getMock('OeLexwareExportImex', array('_getCharset'));
        $oImex->expects($this->any())->method('_getCharset')->will($this->returnValue('UTF-8'));

        $this->assertEquals("zurückgestellt", $oImex->UNITconvertStr("zurückgestellt"));
    }
}
