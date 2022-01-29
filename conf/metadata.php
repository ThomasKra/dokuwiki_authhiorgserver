<?php
/**
 * Options for the authhiorgserver plugin
 *
 * @author HiOrg Server GmbH <support@hiorg-server.de>
 */


$meta['ov'] = array('string', '_pattern' => '/([a-z]{3,4})?/', '_cautionList' => array('plugin____authhiorgserver____ov' => 'danger'));
$meta['ssourl'] = array('string', '_cautionList' => array('plugin____authhiorgserver____ssourl' => 'danger'));

$str_regex = '/^(1';
for ($i = 1; $i <16; $i++)
{
    $str_regex .= '|'.strval(2**$i);
}
$str_regex .= ')$/';
$meta['group_id_users'] = array('numericopt', '_pattern' => $str_regex);
$meta['group_id_admins'] = array('numericopt', '_pattern' => $str_regex);

$meta['syncname'] = array('multichoice','_choices' => array('all','vname','vona','vn'));
