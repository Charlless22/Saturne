<?php
/* Copyright (C) 2023 EVARISK <dev@evarisk.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file        class/certificate.class.php
 * \ingroup     saturne
 * \brief       This file is a CRUD class file for SaturneCertificate (Create/Read/Update/Delete)
 */

// Put here all includes required by your class file
require_once DOL_DOCUMENT_ROOT.'/core/class/commonobject.class.php';

require_once __DIR__ . '/saturnesignature.class.php';

/**
 * Class for SaturneCertificate
 */
class SaturneCertificate extends CommonObject
{
	/**
	 * @var string ID of module.
	 */
	public $module = 'saturne';

	/**
	 * @var string ID to identify managed object.
	 */
	public $element = 'certificate';

	/**
	 * @var string Name of table without prefix where object is stored. This is also the key used for extrafields management.
	 */
	public $table_element = 'saturne_certificate';

	/**
	 * @var int  Does this object support multicompany module ?
	 * 0=No test on entity, 1=Test with field entity, 'field@table'=Test with link by field@table
	 */
	public $ismultientitymanaged = 1;

	/**
	 * @var int  Does object support extrafields ? 0=No, 1=Yes
	 */
	public $isextrafieldmanaged = 1;

	/**
	 * @var string String with name of icon for certificate. Must be the part after the 'object_' into object_certificate.png
	 */
	public $picto = 'certificate@saturne';

    /**
     * @var array Label status of const.
     */
    public $labelStatus;

    /**
     * @var array Label status short of const.
     */
    public $labelStatusShort;

    const STATUS_DELETED = -1;
	const STATUS_DRAFT = 0;
	const STATUS_VALIDATED = 1;
    const STATUS_ARCHIVED = 2;
	const STATUS_EXPIRED = 3;

	/**
	 * @var array  Array with all fields and their property. Do not use it as a static var. It may be modified by constructor.
	 */
	public $fields=array(
		'rowid' 		=> array('type' => 'integer', 'label'=>'TechnicalID', 'enabled'=>'1', 'position'=>1, 'notnull'=>1, 'visible'=>0, 'noteditable'=>'1', 'index'=>1, 'css'=>'left', 'comment'=>"Id"),
		'ref' 			=> array('type' => 'varchar(128)', 'label'=>'Ref', 'enabled'=>'1', 'position'=>10, 'notnull'=>1, 'visible'=>4, 'noteditable'=>'1', 'default'=>'(PROV)', 'index'=>1, 'searchall'=>1, 'showoncombobox'=>'1', 'validate'=>'1', 'comment'=>"Reference of object"),
		'ref_ext'       => array('type' => 'varchar(128)', 'label'=>'RefExt', 'enabled'=>'1', 'position'=>20, 'notnull'=>0, 'visible'=>0,),
		'entity'        => array('type' => 'integer', 'label'=>'Entity', 'enabled'=>'1', 'position'=>30,'notnull'=>1, 'visible'=>0,),
		'date_creation' => array('type' => 'datetime', 'label'=>'DateCreation', 'enabled'=>'1', 'position'=>40, 'notnull'=>1, 'visible'=>0,),
		'tms' 			=> array('type' => 'timestamp', 'label'=>'DateModification', 'enabled'=>'1', 'position'=>50, 'notnull'=>0, 'visible'=>0,),
		'import_key' 	=> array('type' => 'varchar(14)', 'label'=>'ImportId', 'enabled'=>'1', 'position'=>60, 'notnull'=>-1, 'visible'=>0,),
		'status' 		=> array('type' => 'integer', 'label'=>'Status', 'enabled'=>'1', 'position'=>70, 'notnull'=>1, 'visible'=>2, 'index'=>1, 'arrayofkeyval'=>array('0'=>'Brouillon', '1'=>'Valid&eacute;', '9'=>'Annul&eacute;'), 'validate'=>'1',),
		'label' 		=> array('type' => 'varchar(255)', 'label'=>'Label', 'enabled'=>'1', 'position'=>80, 'notnull'=>0, 'visible'=>1, 'searchall'=>1, 'css'=>'minwidth300', 'cssview'=>'wordbreak', 'showoncombobox'=>'2', 'validate'=>'1',),
		'date_start'	=> array('type' => 'datetime', 'label' => 'DateStart', 'enabled' => '1', 'position' => 90, 'notnull' => -1, 'visible' => 1, 'css' => 'minwidth150',),
		'date_end'		=> array('type' => 'datetime', 'label' => 'DateEnd', 'enabled' => '1', 'position' => 100, 'notnull' => -1, 'visible' => 1, 'css' => 'minwidth150',),
		'description' 	=> array('type' => 'html', 'label'=>'Description', 'enabled'=>'1', 'position'=>110, 'notnull'=>0, 'visible'=>3, 'validate'=>'1',),
		'note_public' 	=> array('type' => 'html', 'label'=>'NotePublic', 'enabled'=>'1', 'position'=>120, 'notnull'=>0, 'visible'=>0, 'cssview'=>'wordbreak', 'validate'=>'1',),
		'note_private' 	=> array('type' => 'html', 'label'=>'NotePrivate', 'enabled'=>'1', 'position'=>130, 'notnull'=>0, 'visible'=>0, 'cssview'=>'wordbreak', 'validate'=>'1',),
		'last_main_doc' => array('type' => 'varchar(255)', 'label'=>'LastMainDoc', 'enabled'=>'1', 'position'=>140, 'notnull'=>0, 'visible'=>0,),
		'model_pdf' 	=> array('type' => 'varchar(255)', 'label'=>'Model pdf', 'enabled'=>'1', 'position'=>150, 'notnull'=>-1, 'visible'=>0,),
		'sha256'		=> array('type' => 'text', 'label' => 'Sha256', 'enabled' => '1', 'position' => 160, 'notnull' => -1, 'visible' => 0, 'css' => 'minwidth150',),
		'json'			=> array('type' => 'text', 'label'=>'JSON', 'enabled'=>'1', 'position'=>170, 'notnull'=>0, 'visible'=>0,),
		'element_type'	=> array('type' => 'text', 'label'=>'ElementType', 'enabled'=>'1', 'position'=>180, 'notnull'=>0, 'visible'=>0,),
		'fk_element' 	=> array('type' => 'integer:User:user/class/user.class.php', 'label'=>'FkElement', 'enabled'=>'1', 'position'=>84, 'notnull'=>0, 'visible'=>3, 'index'=>1, 'css'=>'maxwidth500 widthcentpercentminusxx',),
		'fk_product' 	=> array('type' => 'integer:Product:product/class/product.class.php:1', 'label'=>'Product', 'enabled'=>'1', 'position'=>82, 'notnull'=>-1, 'visible'=>3, 'index'=>1, 'css'=>'maxwidth500 widthcentpercentminusxx',),
		'fk_soc'	    => array('type' => 'integer:Societe:societe/class/societe.class.php:1:status=1 AND entity IN (__SHARED_ENTITIES__)', 'label'=>'ThirdParty', 'enabled'=>'1', 'position'=>83, 'notnull'=>-1, 'visible'=>3, 'index'=>1, 'css'=>'maxwidth500 widthcentpercentminusxx',),
		'fk_project'	=> array('type' => 'integer:Project:projet/class/project.class.php:1', 'label'=>'Project', 'enabled'=>'1', 'position'=>81, 'notnull'=>-1, 'visible'=>3, 'index'=>1, 'css'=>'maxwidth500 widthcentpercentminusxx',),
		'fk_user_creat' => array('type' => 'integer:User:user/class/user.class.php', 'label'=>'UserAuthor', 'enabled'=>'1', 'position'=>240, 'notnull'=>1, 'visible'=>0, 'foreignkey'=>'user.rowid',),
		'fk_user_modif'	=> array('type' => 'integer:User:user/class/user.class.php', 'label'=>'UserModif', 'enabled'=>'1', 'position'=>250, 'notnull'=>-1, 'visible'=>0,),
	);

	public $rowid;
	public $ref;
	public $ref_ext;
	public $entity;
	public $date_creation;
	public $tms;
	public $import_key;
	public $status;
	public $label;
	public $date_start;
	public $date_end;
	public $description;
	public $note_public;
	public $note_private;
	public $last_main_doc;
	public $model_pdf;
	public $sha256;
	public $json;
	public $element_type;
	public $fk_element;
	public $fk_product;
	public $fk_soc;
	public $fk_project;
	public $fk_user_creat;
	public $fk_user_modif;

	/**
	 * Constructor
	 *
	 * @param DoliDb $db Database handler
	 */
	public function __construct(DoliDB $db)
	{
		global $conf, $langs;

		$this->db = $db;

		if (empty($conf->global->MAIN_SHOW_TECHNICAL_ID) && isset($this->fields['rowid'])) {
			$this->fields['rowid']['visible'] = 0;
		}
		if (empty($conf->multicompany->enabled) && isset($this->fields['entity'])) {
			$this->fields['entity']['enabled'] = 0;
		}

		// Unset fields that are disabled
		foreach ($this->fields as $key => $val) {
			if (isset($val['enabled']) && empty($val['enabled'])) {
				unset($this->fields[$key]);
			}
		}

		// Translate some data of arrayofkeyval
		if (is_object($langs)) {
			foreach ($this->fields as $key => $val) {
				if (!empty($val['arrayofkeyval']) && is_array($val['arrayofkeyval'])) {
					foreach ($val['arrayofkeyval'] as $key2 => $val2) {
						$this->fields[$key]['arrayofkeyval'][$key2] = $langs->trans($val2);
					}
				}
			}
		}
	}

	/**
	 * Create object into database
	 *
	 * @param  User $user      User that creates
	 * @param  bool $notrigger false=launch triggers after, true=disable triggers
	 * @return int             0 < if KO, ID of created object if OK
	 */
	public function create(User $user, bool $notrigger = false): int
    {
        return $this->createCommon($user, $notrigger);
	}

	/**
	 * Load object in memory from the database
	 *
	 * @param  int         $id   Id object
	 * @param  string|null $ref  Ref
	 * @return int               0 < if KO, 0 if not found, >0 if OK
	 */
	public function fetch($id, string $ref = null): int
	{
		$result = $this->fetchCommon($id, $ref);
		if ($result > 0 && !empty($this->table_element_line)) {
			$this->fetchLines();
		}
		return $result;
	}

	/**
	 * Load object lines in memory from the database
	 *
	 * @return int 0 < if KO, 0 if not found, >0 if OK
	 */
	public function fetchLines(): int
	{
		$this->lines = array();

        return $this->fetchLinesCommon();
	}

	/**
	 * Load list of objects in memory from the database.
	 *
	 * @param  string      $sortorder    Sort Order
	 * @param  string      $sortfield    Sort field
	 * @param  int         $limit        limit
	 * @param  int         $offset       Offset
	 * @param  array       $filter       Filter array. Example array('field'=>'valueforlike', 'customurl'=>...)
	 * @param  string      $filtermode   Filter mode (AND/OR)
	 * @return array|int                 int <0 if KO, array of pages if OK
     * @throws Exception
	 */
	public function fetchAll(string $sortorder = '', string $sortfield = '', int $limit = 0, int $offset = 0, array $filter = array(), string $filtermode = 'AND')
	{
		dol_syslog(__METHOD__, LOG_DEBUG);

		$records = array();

		$sql = "SELECT ";
		$sql .= $this->getFieldList('t');
		$sql .= " FROM ".MAIN_DB_PREFIX.$this->table_element." as t";
		if (isset($this->ismultientitymanaged) && $this->ismultientitymanaged == 1) {
			$sql .= " WHERE t.entity IN (".getEntity($this->element).")";
		} else {
			$sql .= " WHERE 1 = 1";
		}
		// Manage filter
		$sqlwhere = array();
		if (count($filter) > 0) {
			foreach ($filter as $key => $value) {
				if ($key == 't.rowid') {
					$sqlwhere[] = $key." = ".((int) $value);
				} elseif (in_array($this->fields[$key]['type'], array('date', 'datetime', 'timestamp'))) {
					$sqlwhere[] = $key." = '".$this->db->idate($value)."'";
				} elseif ($key == 'customsql') {
					$sqlwhere[] = $value;
				} elseif (strpos($value, '%') === false) {
					$sqlwhere[] = $key." IN (".$this->db->sanitize($this->db->escape($value)).")";
				} else {
					$sqlwhere[] = $key." LIKE '%".$this->db->escape($value)."%'";
				}
			}
		}
		if (count($sqlwhere) > 0) {
			$sql .= " AND (".implode(" ".$filtermode." ", $sqlwhere).")";
		}

		if (!empty($sortfield)) {
			$sql .= $this->db->order($sortfield, $sortorder);
		}
		if (!empty($limit)) {
			$sql .= $this->db->plimit($limit, $offset);
		}

		$resql = $this->db->query($sql);
		if ($resql) {
			$num = $this->db->num_rows($resql);
			$i = 0;
			while ($i < ($limit ? min($limit, $num) : $num)) {
				$obj = $this->db->fetch_object($resql);

				$record = new self($this->db);
				$record->setVarsFromFetchObj($obj);

				$records[$record->id] = $record;

				$i++;
			}
			$this->db->free($resql);

			return $records;
		} else {
			$this->errors[] = 'Error '.$this->db->lasterror();
			dol_syslog(__METHOD__.' '.join(',', $this->errors), LOG_ERR);

			return -1;
		}
	}

	/**
	 * Update object into database
	 *
	 * @param  User $user      User that modifies
	 * @param  bool $notrigger false=launch triggers after, true=disable triggers
	 * @return int             0 < if KO, >0 if OK
	 */
	public function update(User $user, bool $notrigger = false): int
	{
		return $this->updateCommon($user, $notrigger);
	}

	/**
	 * Delete object in database
	 *
	 * @param  User $user       User that deletes
	 * @param  bool $notrigger  false=launch triggers after, true=disable triggers
	 * @return int              0 < if KO, >0 if OK
	 */
	public function delete(User $user, bool $notrigger = false): int
	{
		return $this->deleteCommon($user, $notrigger);
	}

	/**
	 *  Delete a line of object in database
	 *
	 *	@param  User $user       User that delete
	 *  @param  int  $idline	 ID of line to delete
	 *  @param  bool $notrigger  false=launch triggers after, true=disable triggers
	 *  @return int         	 0 < if KO, >0 if OK
	 */
	public function deleteLine(User $user, int $idline, bool $notrigger = false): int
    {
		if ($this->status < 0) {
			$this->error = 'ErrorDeleteLineNotAllowedByObjectStatus';
			return -2;
		}

		return $this->deleteLineCommon($user, $idline, $notrigger);
	}

	/**
	 *	Validate object
	 *
	 *	@param	User	  $user     		User making status change
	 *  @param	int		  $notrigger		1=Does not execute triggers, 0= execute triggers
	 *	@return int						    0 <  if OK, 0=Nothing done, >0 if KO
     *  @throws Exception
	 */
	public function validate(User $user, int $notrigger = 0): int
	{
		global $conf;

		require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';

		$error = 0;

		// Protection
		if ($this->status == self::STATUS_VALIDATED) {
			dol_syslog(get_class($this)."::validate action abandonned: already validated", LOG_WARNING);
			return 0;
		}

		$now = dol_now();

		$this->db->begin();

		// Define new ref
		if (!$error && (preg_match('/^\(?PROV/i', $this->ref) || empty($this->ref))) { // empty should not happen, but when it occurs, the test save life
			$num = $this->getNextNumRef();
		} else {
			$num = $this->ref;
		}
		$this->newref = $num;

		if (!empty($num)) {
			// Validate
			$sql = "UPDATE ".MAIN_DB_PREFIX.$this->table_element;
			$sql .= " SET ref = '".$this->db->escape($num)."',";
			$sql .= " status = ".self::STATUS_VALIDATED;
			if (!empty($this->fields['date_validation'])) {
				$sql .= ", date_validation = '".$this->db->idate($now)."'";
			}
			if (!empty($this->fields['fk_user_valid'])) {
				$sql .= ", fk_user_valid = ".((int) $user->id);
			}
			$sql .= " WHERE rowid = ".($this->id);

			dol_syslog(get_class($this)."::validate()", LOG_DEBUG);
			$resql = $this->db->query($sql);
			if (!$resql) {
				dol_print_error($this->db);
				$this->error = $this->db->lasterror();
				$error++;
			}

			if (!$error && !$notrigger) {
				// Call trigger
				$result = $this->call_trigger('CERTIFICATE_VALIDATE', $user);
				if ($result < 0) {
					$error++;
				}
				// End call triggers
			}
		}

		if (!$error) {
			$this->oldref = $this->ref;

			// Rename directory if dir was a temporary ref
			if (preg_match('/^\(?PROV/i', $this->ref)) {
				// Now we rename also files into index
				$sql = 'UPDATE '.MAIN_DB_PREFIX."ecm_files set filename = CONCAT('".$this->db->escape($this->newref)."', SUBSTR(filename, ".(strlen($this->ref) + 1).")), filepath = 'certificate/".$this->db->escape($this->newref)."'";
				$sql .= " WHERE filename LIKE '".$this->db->escape($this->ref)."%' AND filepath = 'certificate/".$this->db->escape($this->ref)."' and entity = ".$conf->entity;
				$resql = $this->db->query($sql);
				if (!$resql) {
					$error++; $this->error = $this->db->lasterror();
				}

				// We rename directory ($this->ref = old ref, $num = new ref) in order not to lose the attachments
				$oldref = dol_sanitizeFileName($this->ref);
				$newref = dol_sanitizeFileName($num);
				$dirsource = $conf->saturne->dir_output.'/certificate/'.$oldref;
				$dirdest = $conf->saturne->dir_output.'/certificate/'.$newref;
				if (!$error && file_exists($dirsource)) {
					dol_syslog(get_class($this)."::validate() rename dir ".$dirsource." into ".$dirdest);

					if (@rename($dirsource, $dirdest)) {
						dol_syslog("Rename ok");
						// Rename docs starting with $oldref with $newref
						$listoffiles = dol_dir_list($conf->saturne->dir_output.'/certificate/'.$newref, 'files', 1, '^'.preg_quote($oldref, '/'));
						foreach ($listoffiles as $fileentry) {
							$dirsource = $fileentry['name'];
							$dirdest = preg_replace('/^'.preg_quote($oldref, '/').'/', $newref, $dirsource);
							$dirsource = $fileentry['path'].'/'.$dirsource;
							$dirdest = $fileentry['path'].'/'.$dirdest;
							@rename($dirsource, $dirdest);
						}
					}
				}
			}
		}

		// Set new ref and current status
		if (!$error) {
			$this->ref = $num;
			$this->status = self::STATUS_VALIDATED;
		}

		if (!$error) {
			$this->db->commit();
			return 1;
		} else {
			$this->db->rollback();
			return -1;
		}
	}

	/**
	 *	Set draft status
	 *
	 *	@param	User	  $user			    Object user that modify
	 *  @param	int		  $notrigger		1=Does not execute triggers, 0=Execute triggers
	 *	@return	int						    0 < if KO, >0 if OK
     *  @throws Exception
	 */
	public function setDraft(User $user, int $notrigger = 0): int
	{
		// Protection
		if ($this->status <= self::STATUS_DRAFT) {
			return 0;
		}

        $signatory = new SaturneCertificateSignature($this->db);
        $signatory->deleteSignatoriesSignatures($this->id, 'certificate');
		return $this->setStatusCommon($user, self::STATUS_DRAFT, $notrigger, 'CERTIFICATE_UNVALIDATE');
	}

    /**
     *	Set archived status
     *
     *	@param  User $user	    Object user that modify
     *  @param  int  $notrigger 1=Does not execute triggers, 0=Execute triggers
     *	@return	int			    0 < if KO, >0 if OK
     */
    public function setArchived(User $user, int $notrigger = 0): int
    {
        return $this->setStatusCommon($user, self::STATUS_ARCHIVED, $notrigger, 'CERTIFICATE_ARCHIVED');
    }

	/**
	 *  Return a link to the object card (with optionaly the picto)
	 *
	 *  @param  int     $withpicto                  Include picto in link (0=No picto, 1=Include picto into link, 2=Only picto)
	 *  @param  string  $option                     On what the link point to ('nolink', ...)
	 *  @param  int     $notooltip                  1=Disable tooltip
	 *  @param  string  $morecss                    Add more css on link
	 *  @param  int     $saveLastSearchValue       -1=Auto, 0=No save of lastsearch_values when clicking, 1=Save lastsearch_values whenclicking
	 *  @return	string                              String with URL
	 */
	public function getNomUrl(int $withpicto = 0, string $option = '', int $notooltip = 0, string $morecss = '', int $saveLastSearchValue = -1): string
	{
		global $conf, $langs;

		if (!empty($conf->dol_no_mouse_hover)) {
			$notooltip = 1; // Force disable tooltips
		}

		$result = '';

		$label = '<i class="fas fa-user-graduate" style="color: #d35968;"></i> <u>'.$langs->trans("SaturneCertificate").'</u>';
		if (isset($this->status)) {
			$label .= ' '.$this->getLibStatut(5);
		}
		$label .= '<br>';
		$label .= '<b>'.$langs->trans('Ref').':</b> '.$this->ref;

		$url = dol_buildpath('/saturne/view/certificate/certificate_card.php', 1).'?id='.$this->id;

		if ($option != 'nolink') {
			// Add param to save lastsearch_values or not
			$addSaveLastSearchValue = ($saveLastSearchValue == 1 ? 1 : 0);
			if ($saveLastSearchValue == -1 && preg_match('/list\.php/', $_SERVER["PHP_SELF"])) {
				$addSaveLastSearchValue = 1;
			}
			if ($url && $addSaveLastSearchValue) {
				$url .= '&save_lastsearch_values=1';
			}
		}

		$linkclose = '';
		if (empty($notooltip)) {
			if (!empty($conf->global->MAIN_OPTIMIZEFORTEXTBROWSER)) {
				$label = $langs->trans("ShowSaturneCertificate");
				$linkclose .= ' alt="'.dol_escape_htmltag($label, 1).'"';
			}
			$linkclose .= ' title="'.dol_escape_htmltag($label, 1).'"';
			$linkclose .= ' class="classfortooltip'.($morecss ? ' '.$morecss : '').'"';
		} else {
			$linkclose = ($morecss ? ' class="'.$morecss.'"' : '');
		}

		if ($option == 'nolink' || empty($url)) {
			$linkstart = '<span';
		} else {
			$linkstart = '<a href="'.$url.'"';
		}
		$linkstart .= $linkclose.'>';
		if ($option == 'nolink' || empty($url)) {
			$linkend = '</span>';
		} else {
			$linkend = '</a>';
		}

        if ($withpicto) $result .= '<i class="fas fa-user-graduate" style="color: #d35968;"></i>' . ' ';
        $result .= $linkstart;
        if ($withpicto != 2) {
			$result .= $this->ref;
		}

		$result .= $linkend;
		//if ($withpicto != 2) $result.=(($addlabel && $this->label) ? $sep . dol_trunc($this->label, ($addlabel > 1 ? $addlabel : 0)) : '');

		global $action, $hookmanager;
		$hookmanager->initHooks(array('certificatedao'));
		$parameters = array('id'=>$this->id, 'getnomurl'=>$result);
		$reshook = $hookmanager->executeHooks('getNomUrl', $parameters, $this, $action); // Note that $action and $object may have been modified by some hooks
		if ($reshook > 0) {
			$result = $hookmanager->resPrint;
		} else {
			$result .= $hookmanager->resPrint;
		}

		return $result;
	}

	/**
	 *  Return the label of the status
	 *
	 *  @param  int		$mode          0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=Short label + Picto, 6=Long label + Picto
	 *  @return	string 			       Label of status
	 */
	public function getLibStatut(int $mode = 0): string
	{
		return $this->LibStatut($this->status, $mode);
	}

	/**
	 *  Return the status
	 *
	 *  @param	int		$status        Id status
	 *  @param  int		$mode          0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=Short label + Picto, 6=Long label + Picto
	 *  @return string 			       Label of status
	 */
	public function LibStatut(int $status, int $mode = 0): string
	{
		// phpcs:enable
		if (empty($this->labelStatus) || empty($this->labelStatusShort)) {
			global $langs;
			$langs->load("saturne@saturne");
			$this->labelStatus[self::STATUS_DRAFT]     = $langs->transnoentitiesnoconv('StatusDraft');
			$this->labelStatus[self::STATUS_VALIDATED] = $langs->transnoentitiesnoconv('Enabled');
            $this->labelStatus[self::STATUS_ARCHIVED]  = $langs->transnoentitiesnoconv('Archived');

			$this->labelStatusShort[self::STATUS_DRAFT]     = $langs->transnoentitiesnoconv('StatusDraft');
			$this->labelStatusShort[self::STATUS_VALIDATED] = $langs->transnoentitiesnoconv('Enabled');
            $this->labelStatusShort[self::STATUS_ARCHIVED]  = $langs->transnoentitiesnoconv('Archived');
		}

		$statusType = 'status'.$status;
        if ($status == self::STATUS_VALIDATED) $statusType = 'status3';
        if ($status == self::STATUS_ARCHIVED) $statusType  = 'status8';

		return dolGetStatus($this->labelStatus[$status], $this->labelStatusShort[$status], '', $statusType, $mode);
	}

	/**
	 *	Load the info information in the object
	 *
	 *	@param  int  $id ID of object
	 *	@return	void
	 */
	public function info(int $id)
	{
		$sql = "SELECT rowid, date_creation as datec, tms as datem,";
		$sql .= " fk_user_creat, fk_user_modif";
		$sql .= " FROM ".MAIN_DB_PREFIX.$this->table_element." as t";
		$sql .= " WHERE t.rowid = ".($id);

		$result = $this->db->query($sql);
		if ($result) {
			if ($this->db->num_rows($result)) {
				$obj = $this->db->fetch_object($result);
				$this->id = $obj->rowid;

                $this->date_creation     = $this->db->jdate($obj->datec);
                $this->date_modification = $this->db->jdate($obj->datem);
			}

			$this->db->free($result);
		} else {
			dol_print_error($this->db);
		}
	}

	/**
	 * Initialise object with example values
	 * ID must be 0 if object instance is a specimen
	 *
	 * @return void
	 */
	public function initAsSpecimen()
	{
		$this->initAsSpecimenCommon();
	}

	/**
	 * 	Create an array of lines
	 *
	 * 	@return array|int array of lines if OK, <0 if KO
	 */
	public function getLinesArray()
	{
		$this->lines = array();

		$objectline = new SaturneCertificateLine($this->db);
		$result = $objectline->fetchAll('ASC', 'position', 0, 0, array('customsql'=>'fk_certificate = '.($this->id)));

		if (is_numeric($result)) {
			$this->error = $objectline->error;
			$this->errors = $objectline->errors;
			return $result;
		} else {
			$this->lines = $result;
			return $this->lines;
		}
	}

	/**
	 *  Returns the reference to the following non-used object depending on the active numbering module.
	 *
	 *  @return string      		Object free reference
	 */
	public function getNextNumRef(): string
	{
		global $langs, $conf;
		$langs->load("saturne@saturne");

		if (empty($conf->global->SATURNE_CERTIFICATE_ADDON)) {
			$conf->global->SATURNE_CERTIFICATE_ADDON = 'mod_certificate_standard';
		}

		if (!empty($conf->global->SATURNE_CERTIFICATE_ADDON)) {
			$mybool = false;

			$file = $conf->global->SATURNE_CERTIFICATE_ADDON.".php";
			$classname = $conf->global->SATURNE_CERTIFICATE_ADDON;

			// Include file with class
			$dirmodels = array_merge(array('/'), $conf->modules_parts['models']);
			foreach ($dirmodels as $reldir) {
				$dir = dol_buildpath($reldir."core/modules/saturne/");

				// Load file with numbering class (if found)
				$mybool |= @include_once $dir.$file;
			}

			if ($mybool === false) {
				dol_print_error('', "Failed to include file ".$file);
				return '';
			}

			if (class_exists($classname)) {
				$obj = new $classname();
				$numref = $obj->getNextValue($this);

				if ($numref != '' && $numref != '-1') {
					return $numref;
				} else {
					$this->error = $obj->error;
					//dol_print_error($this->db,get_class($this)."::getNextNumRef ".$obj->error);
					return "";
				}
			} else {
				print $langs->trans("Error")." ".$langs->trans("ClassNotFound").' '.$classname;
				return "";
			}
		} else {
			print $langs->trans("ErrorNumberingModuleNotSetup", $this->element);
			return "";
		}
	}
}

require_once DOL_DOCUMENT_ROOT.'/core/class/commonobjectline.class.php';

/**
 * Class SaturneCertificateLine. You can also remove this and generate a CRUD class for lines objects.
 */
class SaturneCertificateLine extends CommonObjectLine
{
	// To complete with content of an object SaturneCertificateLine
	// We should have a field rowid, fk_certificate and position

	/**
	 * @var int  Does object support extrafields ? 0=No, 1=Yes
	 */
	public $isextrafieldmanaged = 0;

	/**
	 * Constructor
	 *
	 * @param DoliDb $db Database handler
	 */
	public function __construct(DoliDB $db)
	{
		$this->db = $db;
	}
}

/**
 * Class SaturneCertificateSignature
 */

class SaturneCertificateSignature extends SaturneSignature
{
	/**
	 * @var string Name of table without prefix where object is stored. This is also the key used for extrafields management.
	 */

	public $object_type = 'certificate';

	/**
	 * @var array Context element object
	 */
	public $context = array();

	/**
	 * @var string String with name of icon for document. Must be the part after the 'object_' into object_document.png
	 */
	public $picto = 'certificate@saturne';

	/**
	 * Constructor
	 *
	 * @param DoliDb $db Database handler
	 */
	public function __construct(DoliDB $db)
	{
		global $conf, $langs;

		$this->db = $db;

		if (empty($conf->global->MAIN_SHOW_TECHNICAL_ID) && isset($this->fields['rowid'])) $this->fields['rowid']['visible'] = 0;
		if (empty($conf->multicompany->enabled) && isset($this->fields['entity'])) $this->fields['entity']['enabled']        = 0;

		// Unset fields that are disabled
		foreach ($this->fields as $key => $val) {
			if (isset($val['enabled']) && empty($val['enabled'])) {
				unset($this->fields[$key]);
			}
		}

		// Translate some data of arrayofkeyval
		if (is_object($langs)) {
			foreach ($this->fields as $key => $val) {
				if (is_array($val['arrayofkeyval'])) {
					foreach ($val['arrayofkeyval'] as $key2 => $val2) {
						$this->fields[$key]['arrayofkeyval'][$key2] = $langs->trans($val2);
					}
				}
			}
		}
	}
}
