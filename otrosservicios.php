<?php 
require_once('conexion.php'); 
$con = mysqli_connect($hostname_conexion,$username_conexion,$password_conexion,$database_conexion);
mysqli_set_charset($con, "utf8");        
$database = mysqli_select_db($con,$database_conexion);
$httphost = filter_input(INPUT_SERVER, 'HTTP_HOST');
?>
<html>
<head>
  <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
  <!--*************** Ventanas Modales**********************-->
  <link rel="stylesheet" type="text/css" href="/servicios/css/ventanas-modales.css">
  <script type="text/javascript" src="/servicios/code/jquery-1.8.0.min.js"></script>
  <script type="text/javascript" src="/servicios/code/ventanas-modales.js"></script>   
  <!--**************************************************************-->
  <link rel="stylesheet" href="/servicios/code/jquery-ui.css">
  <script src="/servicios/code/jquery-ui.js"></script>
  <script>
  $(function() {
    $( "#tabs" ).tabs();
  });
  $(function() {
    $( "#accordion" ).accordion({
      heightStyle: "content"
    });
  });
  $(function() {
    $( "#accordion1" ).accordion({
      heightStyle: "content"
    });
  });
  </script>
  <link rel="stylesheet" href="/servicios/css/style.css">
  <link rel="stylesheet" href="/servicios/css/style_responsive.css">	
</head>
<body>
<div id="tabs">
	<ul>
		<li><a href="#tabs-1">Por Sector</a></li>
		<li><a href="#tabs-2">Por Tema</a></li>
	</ul>
<?php
	echo "<div id='tabs-1'><div id='accordion1'>";	
	$sqlSector = "SELECT DISTINCT poder.podvnombre "
    . "FROM idepcoor.gen_entidad entidad, idepcoor.gepr_ta_respuesta respuesta, idepcoor.gepr_ta_rstadetalle rstadetalle, idepcoor.ge_poder poder "
    . "WHERE (poder.podnid=entidad.podnid)and(entidad.entnid=respuesta.entnid)and(respuesta.pk_id_rsta=rstadetalle.pk_id_rsta)and(respuesta.pk_id_preg='4')and(rstadetalle.ch_rstadet_accesolibre='SI')";	                                
	$resultSector = mysqli_query($con,$sqlSector);
 	while ($row = mysqli_fetch_row($resultSector))                
	{
		$sector=$row[0];        
		$sqlListaSector = "SELECT rstadetalle.vc_rstadet_nombre,rstadetalle.vc_rstadet_descripcion,rstadetalle.ch_pregtip_codigo,clasificacion.vc_clasific_categoria,entidad.entvnombre,rstadetalle.vc_rstadet_direcurl,CASE WHEN b_direcurl_estado='1' THEN 'activo.png' ELSE 'inactivo.png' END "
      . "FROM idepcoor.gen_entidad entidad, idepcoor.gepr_ta_respuesta respuesta, idepcoor.gepr_ta_rstadetalle rstadetalle, idepcoor.gepr_ta_clasific clasificacion, idepcoor.ge_poder poder "
      . "WHERE (respuesta.entnid=entidad.entnid)and(rstadetalle.pk_id_rsta=respuesta.pk_id_rsta)and(rstadetalle.pk_id_clasific=clasificacion.pk_id_clasific)and(poder.podnid=entidad.podnid)and(respuesta.pk_id_preg='4')and(rstadetalle.ch_rstadet_accesolibre='SI')and(poder.podvnombre='$sector')";						
		$resultListaSector = mysqli_query($con,$sqlListaSector); 		
		echo "<h3>$sector</h3><div><table><thead><tr><th>Nombre del Servicio</th><th>Descripción</th><th>Tipo de Servicio</th><th>Tema</th><th>Proporcionado por</th><th>Estado</th><th>Dirección del Servicio</th><th>Acceso</th></tr></thead><tbody>"; 	
		while ($row = mysqli_fetch_row($resultListaSector))   
		{		
			if($row[6]=='activo.png'){$title="El servicio está operativo";$t="1";}else{$title="El servicio está temporalmente inactivo";$t="3";};			
			$item=base64_encode("WMS".$row[5]);			
			echo "<tr><td>".$row[0]."</td><td>".$row[1]."</td><td>".$row[2]."</td><td>".$row[3]."</td><td>".$row[4]."</td>";
			echo "<td><img title='".$title."' src='/images/".$row[6]."'></td><td><div id='divContenido'><a href='http://".$httphost."/servicios/modalservicio.php?serv=$item' class='clsVentanaIFrame clsBoton' rel='Detalle de Servicio' on>Ver dirección</a></div></td>";
			echo "<td>";						
			if($t==="1")
			{
				if ((strpos($row[5],'MapServer')!==false)and($row[2]==='Servicios REST'))
				{ 
					echo "<div id='divContenido'><a href='http://mapas.geoidep.gob.pe/mapasperu/?config=viewer_wms&wmsuri=".$row[5]."&wmstitle=".$row[0]."&t=1' target='_blank' class='clsBoton' title='WMS : ".$row[5]."' >Ver Mapa</a></div>"; 
				}else{
					echo "<div id='divContenido'><a href='" .$row[5]."' target='_blank' class='clsBoton'>Acceder</a></div>";		
				}
			}
			echo "</td></tr>";										
		}                             
		echo "</tbody></table></div> ";
	}
	echo "</div></div>";     
	echo "<div id='tabs-2'><div id='accordion'>";
	$sqlClasificacion = "SELECT DISTINCT clasificacion.vc_clasific_categoria, clasificacion.pk_id_clasific "
		. "FROM idepcoor.gepr_ta_clasific clasificacion, idepcoor.gepr_ta_rstadetalle rstadetalle, idepcoor.gepr_ta_respuesta respuesta "
    . "WHERE (rstadetalle.pk_id_clasific=clasificacion.pk_id_clasific)and(respuesta.pk_id_rsta=rstadetalle.pk_id_rsta)and(respuesta.pk_id_preg='4')and(rstadetalle.ch_rstadet_accesolibre='SI')";   	
	$resultClasificacion = mysqli_query($con,$sqlClasificacion);                  
	while ($row = mysqli_fetch_row($resultClasificacion))            
	{
		$clasificacion=$row[0];		
		$sql_ = "SELECT rstadetalle.vc_rstadet_nombre,rstadetalle.vc_rstadet_descripcion,rstadetalle.ch_pregtip_codigo,entidad.entvnombre,rstadetalle.vc_rstadet_direcurl,CASE WHEN b_direcurl_estado='1' THEN 'activo.png' ELSE 'inactivo.png' END "
        . "FROM idepcoor.gen_entidad entidad, idepcoor.gepr_ta_respuesta respuesta, idepcoor.gepr_ta_rstadetalle rstadetalle, idepcoor.gepr_ta_clasific clasificacion, idepcoor.ge_poder poder "
        . "WHERE (respuesta.entnid=entidad.entnid) and (rstadetalle.pk_id_rsta=respuesta.pk_id_rsta) and (rstadetalle.pk_id_clasific=clasificacion.pk_id_clasific) and (poder.podnid=entidad.podnid) and (respuesta.pk_id_preg='4')and(rstadetalle.ch_rstadet_accesolibre='SI')and(clasificacion.vc_clasific_categoria='$clasificacion')";                                                         	
		$result_ = mysqli_query($con,$sql_);    		
		echo "<h3>$clasificacion</h3><div><table><thead><tr><th>Nombre del Servicio</th><th>Descripción</th><th>Tipo de Servicio</th><th>Proporcionado por</th><th>Estado</th><th>Dirección del Servicio</th><th>Acceso</th></tr></thead><tbody>";        		            
		while ($row = mysqli_fetch_row($result_))
		{
			if($row[5]=='activo.png'){$title="El servicio está operativo";$t="1";}else{$title="El servicio está temporalmente inactivo";$t="3";};	
			$item=base64_encode("WMS".$row[4]);						
			echo "<tr><td>".$row[0]."</td><td>".$row[1]."</td><td>".$row[2]."</td><td>".$row[3]."</td>";
			echo "<td><img title='".$title."' src='/images/".$row[5]."'></td><td><div id='divContenido'><a href='http://".$httphost."/servicios/modalservicio.php?serv=$item' class='clsVentanaIFrame clsBoton' rel='Detalle de Servicio' on>Ver dirección</a></div></td>";					
			echo "<td>";						
			if($t==="1")
			{
				if ((strpos($row[4],'MapServer')!==false)and($row[2]==='Servicios REST'))
				{ 
					echo "<div id='divContenido'><a href='http://mapas.geoidep.gob.pe/mapasperu/?config=viewer_wms&wmsuri=".$row[4]."&wmstitle=".$row[0]."&t=1' target='_blank' class='clsBoton' title='Web Service : ".$row[4]."' >Ver Mapa</a></div>"; 
				}else{
					echo "<div id='divContenido'><a href='" .$row[4]."' target='_blank' class='clsBoton'>Acceder</a></div>";		
				}
			}
			echo "</td></tr>";
		}            
		echo "</tbody></table></div> ";
	}
	echo "</div></div>"; 
?>    
</div>
<p class='home-idep-texto'>Estado de los servicios revisados el día <?php echo date('d-m-Y'); ?> a las 12:00 a.m.</p>
</body>
</html>
