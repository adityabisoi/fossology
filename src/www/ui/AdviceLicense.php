<?php
/***********************************************************
 Copyright (C) 2008-2014 Hewlett-Packard Development Company, L.P.
 Copyright (C) 2014 Siemens AG

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 version 2 as published by the Free Software Foundation.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License along
 with this program; if not, write to the Free Software Foundation, Inc.,
 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 ***********************************************************/

namespace Fossology\UI;

use Fossology\Lib\Dao\LicenseDao;
use Fossology\Lib\Db\DbManager;
use Fossology\Lib\Plugin\DefaultPlugin;
use Symfony\Component\HttpFoundation\Request;

class AdviceLicense extends DefaultPlugin
{
  const NAME = "advice_license";

  function __construct()
  {
    parent::__construct(self::NAME, array(
        self::TITLE => "Advice License",
        self::MENU_LIST => "Organize::License",
        self::REQUIRES_LOGIN => true,
        self::DEPENDENCIES => array(\ui_menu::NAME)
    ));
  }

  /**
   * @param Request $request
   * @return Response
   */
  protected function handle(Request $request)
  {
    $rf = GetParm('rf', PARM_INTEGER);
    if (empty($rf))
    {
      $vars = array(
          'aaData' => json_encode($this->getArrayArrayData($_SESSION['GroupId']))
      );
      return $this->render('advice_license.html.twig', $this->mergeWithDefault($vars));
    }

    $vars = $this->getDataRow($_SESSION['GroupId'], $rf);
    if ($vars === false)
    {
      throw new Exception('invalid license candidate');
    }


    if (GetParm('save', PARM_STRING))
    {
      try{
        $vars = $this->saveInput($vars);
        $vars['message'] = 'Successfully updated.';
      }
      catch(\Exception $e)
      {
        $vars['message'] = $e->getMessage();
      }
    }
    
    $vars['uri'] = Traceback_uri() .'?mod='. Traceback_parm();
    return $this->render('advice_license-edit.html.twig', $this->mergeWithDefault($vars));
  }

  
  private function getArrayArrayData($groupId)
  {
    $sql = "SELECT rf_pk,rf_shortname,rf_fullname,rf_text,rf_url,marydone FROM license_candidate WHERE group_fk=$1";
    /** @var DbManager */
    $dbManager = $this->container->get('db.manager');
    $dbManager->prepare($stmt=__METHOD__,$sql);
    $res = $dbManager->execute($stmt,array($groupId));
    $aaData = array();
    while($row=$dbManager->fetchArray($res))
    {
      $link = Traceback_uri() . '?mod=' . Traceback_parm() . '&rf='.$row['rf_pk'];
      $edit = '<a href="'.$link.'"><img border="0" src="images/button_edit.png"></a>';
      $aaData[] = array($edit,htmlentities($row['rf_shortname']),
          htmlentities($row['rf_fullname']), 
          '<div style="overflow-y:scroll;max-height:150px;margin:0px;">'.nl2br(htmlentities($row['rf_text'])).'</div>',
          htmlentities($row['rf_url']),
          $this->bool2checkbox($dbManager->booleanFromDb($row['marydone'])));
    }
    $dbManager->freeResult($res);
    return $aaData;
  }
  
  
  private function getDataRow($groupId,$licId)
  {
    $sql = "SELECT rf_pk,rf_shortname,rf_fullname,rf_text,rf_url,marydone FROM license_candidate WHERE group_fk=$1 AND rf_pk=$2";
    /** @var DbManager */
    $dbManager = $this->container->get('db.manager');
    $dbManager->prepare($stmt=__METHOD__,$sql);
    $row = $dbManager->getSingleRow($stmt,array($groupId,$licId),__METHOD__);
    if (false !== $row)
    {
      $row['marydone'] = $dbManager->booleanFromDb($row['marydone']);
    }
    return $row;
  }
  
  
  private function bool2checkbox($bool)
  {
    $check = $bool ? ' checked="checked"': '';
    return '<input type="checkbox"'.$check.' disabled="disabled"/>';
  }

  /**
   * @return array $newRow
   * 
   */
  private function saveInput($oldRow)
  {
    $shortname = GetParm('shortname', PARM_STRING);
    $fullname = GetParm('fullname', PARM_STRING);
    $rfText = GetParm('rf_text', PARM_STRING);
    $url = GetParm('rf_url', PARM_STRING);
    $marydone = GetParm('marydone', PARM_INTEGER);
    
    if(empty($shortname) || empty($fullname) || empty($rfText)){
      throw new \Exception('missing parameter');
    }

    /** @var LicenseDao $licenseDao */
    $licenseDao = $this->container->get('dao.license');

    $ok = ($oldRow['rf_shortname']==$shortname);
    if (!$ok)
    {  
      $ok = $licenseDao->isNewLicense($shortname);
    }
    if(!$ok)
    {
      throw new \Exception('shortname already in use');
    }
    
    $licenseDao->updateCandidate($oldRow['rf_pk'],$shortname,$fullname,$rfText,$url,$marydone);
    return array('rf_pk'=>$oldRow['rf_pk'],'rf_shortname'=>$shortname,'rf_fullname'=>$fullname,'rf_text'=>$rfText,'rf_url'=>$url,'marydone'=>$marydone);            
  }

}

register_plugin(new AdviceLicense());
