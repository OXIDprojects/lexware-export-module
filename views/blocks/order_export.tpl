[{$smarty.block.parent}]

<form name="myedit2" id="myedit2" action="[{$oViewConf->getSelfLink()}]" method="post" >
    [{$oViewConf->getHiddenSid()}]
    <input type="hidden" name="cl" value="order_overview">
    <input type="hidden" name="fnc" value="oeLexwareExportExportLex">
    <input type="hidden" name="oxid" value="[{$oxid}]">
    <table cellspacing="0" cellpadding="0" style="padding-top: 5px; padding-left: 5px; padding-right: 5px; border : 1px #A9A9A9; border-style : solid solid solid solid;" width="220">
        <tr>
            <td class="edittext">
                <b>[{oxmultilang ident="ORDER_OVERVIEW_XMLEXPORT"}]</b>
            </td>
            <td valign="top" class="edittext">
                [{oxmultilang ident="ORDER_OVERVIEW_FROMORDERNUM"}]<br>
                <input type="text" class="editinput" size="15" maxlength="15" name="ordernr" value=""><br>
                [{oxmultilang ident="ORDER_OVERVIEW_TILLORDERNUM"}]<br>
                <input type="text" class="editinput" size="15" maxlength="15" name="toordernr" value=""><br><br>
                <input type="submit" class="edittext" name="save" value="[{oxmultilang ident="ORDER_OVERVIEW_EXPORT"}]">
            </td>
        </tr>
    </table>
</form>
