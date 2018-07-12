<?php
require_once('servicios/conexion.php');
$con = mysqli_connect($hostname_conexion,$username_conexion,$password_conexion,$database_conexion);
mysqli_set_charset($con, "utf8"); 
$database = mysqli_select_db($con,"idepcoor");
?>
<html>
<head>
	<link rel="stylesheet" href="/servicios/css/style.css">
	<link rel="stylesheet" href="/servicios/css/style_responsive.css">	
	<link rel="stylesheet" type="text/css" href="/servicios/css/ventanas-modales.css">		
</head>
<body>  
<?php
if (is_numeric($entidad)) 
{
			
	$sqlSector = "SELECT rstadetalle.vc_rstadet_nombre,rstadetalle.vc_rstadet_direcurl,rstadetalle.vc_rstadet_descripcion,rstadetalle.ch_pregtip_codigo,rstadetalle.vc_rsta_outputformat "
	. "FROM idepcoor.gen_entidad entidad, idepcoor.gepr_ta_respuesta respuesta, idepcoor.gepr_ta_rstadetalle rstadetalle "
	. "WHERE (respuesta.entnid=entidad.entnid)and(rstadetalle.pk_id_rsta=respuesta.pk_id_rsta)and(entidad.entccodigo=$entidad)and((rstadetalle.ch_rstadet_accesolibre='SI')or(rstadetalle.ch_rstadet_accesolibre IS NULL))";		
	$resultSector = mysqli_query($con,$sqlSector);
	$num_servicios = mysqli_num_rows($resultSector);	
	if($num_servicios>0){			
		echo "<div style='font-size: 12px;'><table><thead><tr><th>Nombre del Servicio</th><th>Descripción</th><th>Acceso</th></tr></thead><tbody>"; 
		while ($row=mysqli_fetch_row($resultSector))
		{
			echo "<tr><td>";if(($row[3]=='WMS')or($row[3]=='WFS')) {echo "[".$row[3]."]";};
			echo " ".$row[0]. "<a href='".$row[1]."' target='_blank'> (Ver URL)</a></td><td>".$row[2]."</td> ";		
			if ($row[3]=='WMS')
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
				if ($t !== "3") { 
					$verresultado = "<div id='divContenido'><a href='http://mapas.geoidep.gob.pe/mapasperu/?config=viewer_wms&wmsuri=".$wmsuri."&wmstitle=".$wmstitle."&t=".$t."' target='_blank' class='clsBotonSearch' title='WMS : ".$row[1]."' >Ver Mapa</a></div>"; 
				} else { 						
					$verresultado = "<div id='divContenido'><a href='".$row[1]."' target='_blank' class='clsBotonSearch' >Acceder</a></div>";
				};
			}elseif ($row[3]=='WFS') {											
				$tiposoutput=  explode(",", $row[4]);
				$select="<option value='0'>Elegir formato de descarga</option>";
				$i =0;
				foreach($tiposoutput as $tipo){
					if($i>0)
						$select=$select."<option value='".$row[1]."&outputFormat=".$tipo."'>".$tipo."</option>\n";
					$i++;
				}
				$verresultado = "<select onChange='download(this.value)'>".$select."</select>";
			}else{
				$verresultado = "<div id='divContenido'><a href='".$row[1]."' target='_blank' class='clsBotonSearch' >Acceder</a></div>";
			}				
			echo "<td>".$verresultado."</td></tr>";				
		}						
		echo "</tbody></table></div> ";		
	}
}
?>
<script>
function download(d) {
		if (d == 'Select document') return;
		if(d!="0")
		window.open(d,'_blank');
}
</script>
</body>
</html>
