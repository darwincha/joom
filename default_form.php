<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_search
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('bootstrap.tooltip');

$lang = JFactory::getLanguage();
$upper_limit = $lang->getUpperLimitSearchWord();
include 'SpellCorrector.php';
require_once('conexion.php'); 
$con = mysqli_connect($hostname_conexion,$username_conexion,$password_conexion,$database_conexion);
mysqli_set_charset($con, "utf8");
$database = mysqli_select_db($con,$database_conexion);

?>
<link rel="stylesheet" type="text/css" href="/servicios/css/ventanas-modales.css">  
<form id="searchForm" action="<?php echo JRoute::_('index.php?option=com_search'); ?>" method="post">
	<div class="btn-toolbar">
		<div class="btn-group pull-left">
			<input type="text" name="searchword" title="<?php echo JText::_('COM_SEARCH_SEARCH_KEYWORD'); ?>" placeholder="<?php echo JText::_('COM_SEARCH_SEARCH_KEYWORD'); ?>" id="search-searchword" size="30" maxlength="<?php echo $upper_limit; ?>" value="<?php echo $this->escape($this->origkeyword); ?>" class="inputbox" />
		</div>
		<div class="btn-group pull-left">
			<button name="Search" onclick="this.form.submit()" class="btn hasTooltip" title="<?php echo JHtml::_('tooltipText', 'COM_SEARCH_SEARCH');?>">
				<span class="icon-search"></span>
				<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>
			</button>
		</div>
		<input type="hidden" name="task" value="search" />
		<div class="clearfix"></div>
	</div>
	<?php
		$cadena = $this->escape($this->origkeyword);
		$cadena = htmlspecialchars($cadena);		
		echo "<h2>Resultados de la busqueda del término '".$cadena."'</h2>";
		/* PORTALES */
		$sqlportales = "SELECT rstadetalle.vc_rstadet_nombre,rstadetalle.vc_rstadet_direcurl,'Geoportal','Geoportal',entidad.entvnombre,rstadetalle.vc_rstadet_descripcion "
		. "FROM idepcoor.gen_entidad entidad, idepcoor.gepr_ta_respuesta respuesta, idepcoor.gepr_ta_rstadetalle rstadetalle "
		. "WHERE ((rstadetalle.vc_rstadet_nombre like '%$cadena%')or(rstadetalle.vc_rstadet_descripcion like '%$cadena%')or(rstadetalle.vc_rstadet_direcurl like '%$cadena%')or(rstadetalle.vc_rstadet_keywords like '%$cadena%')) "
		. "and(respuesta.entnid=entidad.entnid)and(rstadetalle.pk_id_rsta=respuesta.pk_id_rsta)and((rstadetalle.ch_pregtip_codigo='SI')OR(rstadetalle.ch_pregtip_codigo='NO')) ";		
		$resultportales = mysqli_query($con,$sqlportales);
		$num_portales = mysqli_num_rows($resultportales);		
		/* SERVICIOS WEB */				
		$sqlServicios = "SELECT rstadetalle.vc_rstadet_nombre,rstadetalle.vc_rstadet_direcurl,rstadetalle.ch_pregtip_codigo, "
		. "clasificacion.vc_clasific_categoria,entidad.entvnombre,rstadetalle.vc_rstadet_descripcion,rstadetalle.vc_rsta_outputformat,rstadetalle.b_direcurl_estado "
		. "FROM idepcoor.gen_entidad entidad, idepcoor.gepr_ta_respuesta respuesta, idepcoor.gepr_ta_rstadetalle rstadetalle, idepcoor.gepr_ta_clasific clasificacion "
		. "WHERE ((rstadetalle.vc_rstadet_nombre like '%$cadena%')or(rstadetalle.vc_rstadet_descripcion like '%$cadena%')or(rstadetalle.vc_rstadet_direcurl like '%$cadena%')or(rstadetalle.vc_rstadet_keywords like '%$cadena%')) "
		. "and((rstadetalle.ch_rstadet_accesolibre>='SI')or(rstadetalle.ch_rstadet_accesolibre is null))and(respuesta.entnid=entidad.entnid)and(rstadetalle.pk_id_rsta=respuesta.pk_id_rsta)and(rstadetalle.pk_id_clasific=clasificacion.pk_id_clasific)";				
		$resultServicios = mysqli_query($con,$sqlServicios);
		$num_servicios = mysqli_num_rows($resultServicios);		
		$total_servicios = $num_portales+$num_servicios;				
		if($total_servicios>0){		
			$total_servicios = $total_servicios.' Servicios Web y Aplicaciones encontrados. (Click para expandir)';
			echo '<p>{slider title="'.$total_servicios.'" class="red solid" open="false"}</p>';			
			echo '<ol>';
			while ($row = mysqli_fetch_row($resultportales))                
			{				
				echo '<li><b>'.$row[0].'</b> <a href="'.$row[1].'" target="_blank">(Ver URL)</a><br />';						
				echo '<div class="home-idep-busqueda"><table><tr><td>';
				echo ''.$row[5].'<br />';				
				echo '<b>FORMATO:</b> '.$row[2].' / <b>TEMA:</b> '.$row[3].'<br />';
				echo '<b>ENTIDAD PROVEEDORA:</b> '.$row[4].'<br />';				
				echo '</td><td width="12%"><div id="divContenido"><a href="'.$row[1].'" target="_blank" class="clsBotonSearch" >Acceder</a></div></td></tr></table></div></li><hr>';							
			}
			while ($row = mysqli_fetch_row($resultServicios))                
			{		
				echo '<li><b>'.$row[0].'</b> <a href="'.$row[1].'" target="_blank">(Ver URL)</a><br />';						
				echo '<div class="home-idep-busqueda"><table><tr><td>';
				echo ''.$row[5].'<br />';								
				echo '<b>FORMATO:</b> '.$row[2].' / <b>TEMA:</b> '.$row[3].'<br />';
				echo '<b>ENTIDAD PROVEEDORA:</b> '.$row[4].'<br />';					
				if ($row[2]=='WMS')
				{
					if (strpos($row[1], '/MapServer/') !== false) 
					{
						$webservicewmsfull = str_replace("/services/", "/rest/services/", $row[1]); //añadir rest								
						$webservicewmspart1 = explode("/WMSServer", $webservicewmsfull); //cortar hasta MapServer							
						$wmsuri = $webservicewmspart1[0];
						$wmstitle = $row[0];
						$t = "1";
					}		
					elseif (strpos($row[1], '/wms?') !== false) {											
						$nombrecapa = explode("&layers=", $row[1]); //&layers=
						$wmstitle = $nombrecapa[1];						
						$webservicewmsfull = explode("/wms?", $row[1]); //cortar hasta /wms?
						$wmsuri = $webservicewmsfull[0]."/wms?service=WMS&request=GetMap"; //añadir getMap									
						$t = "2";
					}						
					else
					{
						$t = "3";
					};							
					if (($t !== "3")and($row[7]==='1')) { 
						$verresultado = "<div id='divContenido'><a href='http://mapas.geoidep.gob.pe/mapasperu/?config=viewer_wms&wmsuri=".$wmsuri."&wmstitle=".$wmstitle."&t=".$t."' target='_blank' class='clsBotonSearch' title='WMS : ".$row[1]."' >Ver Mapa</a></div>"; 
					} else { 						
						$verresultado = "<div id='divContenido'><a href='".$row[1]."' target='_blank' class='clsBotonSearch' >Acceder</a></div>";
					};
				}elseif ($row[2]=='WFS') {											
					$tiposoutput=  explode(",", $row[6]);
					$select="<option value='0'>Elegir formato de descarga</option>";
					$i =0;
					foreach($tiposoutput as $tipo){
						if($i>0)
							$select=$select."<option value='".$row[1]."&outputFormat=".$tipo."'>".$tipo."</option>\n";
						$i++;
					}
					$verresultado = "<select onChange='download(this.value)'>".$select."</select>";
				}elseif (($row[2]==='Servicios REST')and(strpos($row[1],'MapServer')!==false)) {						
					$verresultado = "<div id='divContenido'><a href='http://mapas.geoidep.gob.pe/mapasperu/?config=viewer_wms&wmsuri=".$row[1]."&wmstitle=".$row[0]."&t=1' target='_blank' class='clsBotonSearch' title='Web Service : ".$row[1]."' >Ver Mapa</a></div>"; 								
				}else{
					$verresultado = "<div id='divContenido'><a href='".$row[1]."' target='_blank' class='clsBotonSearch' >Acceder</a></div>";
				}	
				echo '</td><td width="12%" >'.$verresultado.'</td></tr></table></div></li><hr>';								
			}		
			echo '</ol>';
			echo "<h4>Encuentra más servicios y aplicaciones en nuestro <a href='/servicios-idep/catalogo-nacional-de-servicios-web/servicios-de-visualizacion-wms'>catálogo de Servicios Web</a></h4>";
			echo '<p>{/sliders}</p>';
		}
		/* METADATOS */				
		$url='http://192.168.11.80/metadatos/srv/eng/csw?service=CSW&version=2.0.2&request=GetRecords&namespace=xmlns%28csw=http://www.opengis.net/cat/csw%29&resultType=results&outputSchema=http://www.isotc211.org/2005/gmd&outputFormat=application/xml&maxRecords=30&elementSetName=full&constraintLanguage=CQL_TEXT&constraint_language_version=1.1.0&typeNames=csw:Record&constraint=AnyText%20like%20%27'.$cadena.'%27';
		$url = preg_replace("/ /", "%20", $url);		
		$homepage = file_get_contents($url);
		$xml = simplexml_load_string(strval($homepage));
		$xml->registerXPathNamespace('csw', 'http://www.opengis.net/cat/csw/2.0.2');
		$xml->registerXPathNamespace('gmd', "http://www.isotc211.org/2005/gmd");
		$xml->registerXPathNamespace('gml', "http://www.opengis.net/gml/3.2");
		$xml->registerXPathNamespace('gts', "http://www.isotc211.org/2005/gts");
		$xml->registerXPathNamespace('gco', "http://www.isotc211.org/2005/gco");		
		$total_metadatos = $xml->xpath("//csw:SearchResults")[0]["numberOfRecordsMatched"];	
		$total_metadatos = $total_metadatos.' Metadatos encontrados. (Click para expandir)';
		echo '<p>{slider title="'.$total_metadatos.'" class="red solid" open="false"}</p>';					
		echo '<ol>';
		foreach($xml->xpath("//gmd:MD_Metadata") as $entrie){			
			$id = $entrie->xpath("gmd:fileIdentifier")[0]->xpath("gco:CharacterString")[0];			
			$title = $entrie->xpath("gmd:identificationInfo")[0]->xpath("gmd:MD_DataIdentification")[0]->
					 xpath("gmd:citation")[0]->xpath("gmd:CI_Citation")[0]->xpath("gmd:title")[0]->xpath("gco:CharacterString")[0];					 
			$abstract = $entrie->xpath("gmd:identificationInfo")[0]->xpath("gmd:MD_DataIdentification")[0]->xpath("gmd:abstract")[0]->xpath("gco:CharacterString")[0];			
			$entidad = $entrie->xpath("gmd:contact")[0]->xpath("gmd:CI_ResponsibleParty")[0]->xpath("gmd:organisationName")[0]->xpath("gco:CharacterString")[0];			
			$category = $entrie->xpath("gmd:identificationInfo")[0]->xpath("gmd:MD_DataIdentification")[0]->xpath("gmd:topicCategory")[0]->xpath("gmd:MD_TopicCategoryCode")[0];
			echo '<li><b>'.$title.'</b> <a href="http://catalogo.geoidep.gob.pe/metadatos/srv/spa/catalog.search#/metadata/'.$id.'" target="_blank">(Ver URL)</a><br />';						
			echo '<div class="home-idep-busqueda"><table><tr><td>';
			echo ''.$abstract.'<br />';				
			echo '<b>FORMATO:</b> Metadato / <b>TEMA:</b> '.$category.'<br />';
			echo '<b>ENTIDAD PROVEEDORA:</b> '.$entidad.'<br />';				
			echo '</td><td width="12%"><div id="divContenido"><a href="http://catalogo.geoidep.gob.pe/metadatos/srv/spa/catalog.search#/metadata/'.$id.'" target="_blank" class="clsBotonSearch" >Ver Metadato</a></div></td></tr></table></div></li><hr>';			
		}
		echo '</ol>';
		$total_metadatosdevueltos = $xml->xpath("//csw:SearchResults")[0]["numberOfRecordsReturned"];					
		echo "<h4>Se muestran $total_metadatosdevueltos de un total de $total_metadatos Para ver más accede al <a href='http://catalogo.geoidep.gob.pe/'>catálogo</a></h4>";				
		echo '<p>{/sliders}</p>';		
		/* INSERTAR EL TÉRMINO BUSCADO PARA FUTURAS ESTADÍSTICAS */								
		$sqlinsertcon = "INSERT INTO idepcoor.idep_bus_servicios (vc_bus_consulta, vc_bus_ip) VALUES ('".$cadena."', '".$_SERVER['REMOTE_ADDR']."')";							
		mysqli_query($con,$sqlinsertcon);		
	?>	
	<div class="searchintro<?php echo $this->params->get('pageclass_sfx'); ?>">
		<?php if (!empty($this->searchword)) : ?>
			<p>
				<?php echo JText::plural('COM_SEARCH_SEARCH_KEYWORD_N_RESULTS', '<span class="badge badge-info">' . $this->total . '</span>'); ?>
			</p>
		<?php endif; ?>
	</div>
	<?php if ($this->total > 0) : ?>
		<div class="form-limit">
			<label for="limit">
				<?php echo JText::_('JGLOBAL_DISPLAY_NUM'); ?>
			</label>
			<?php echo $this->pagination->getLimitBox(); ?>
		</div>
		<p class="counter">
			<?php echo $this->pagination->getPagesCounter(); ?>
		</p>
	<?php endif; ?>	
	<?php 
		if(($total_servicios==0)and($total_metadatosdevueltos==0)){	
			echo "<h3>Quizás buscabas: <a href='component/search/?searchword=".SpellCorrector::correct("$cadena")."&searchphrase=all&Itemid=206'>".SpellCorrector::correct("$cadena")."</a></h3>"; 
		}
	?>		
</form>
<script>
function download(d) {
    if (d == 'Select document') return;
		if(d!="0")
		window.open(d,'_blank');
}
</script>
