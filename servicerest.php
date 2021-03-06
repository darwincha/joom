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
  </script><link rel="stylesheet" href="/servicios/code/jquery-ui.css">  
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
		<li><a href="#tabs-1">REST por Entidad</a></li>
    <li><a href="#tabs-2">REST por Clasificación</a></li>
	</ul>
<?php  
	echo "<div id='tabs-1'><div id='accordion1'>";	
	$sqlEntidad = "SELECT DISTINCT entidad.entvnombre "
	. "FROM idepcoor.gen_entidad entidad, idepcoor.gepr_ta_respuesta respuesta, idepcoor.gepr_ta_rstadetalle rstadetalle "
	. "WHERE (entidad.entnid=respuesta.entnid)and(respuesta.pk_id_rsta=rstadetalle.pk_id_rsta)and(rstadetalle.ch_pregtip_codigo='Servicios REST') "
	. "ORDER BY entidad.entvnombre";			
	$resultEntidad = mysqli_query($con,$sqlEntidad);
	while ($row = mysqli_fetch_row($resultEntidad))                
	{
		$entidad=$row[0];     
		$sqlListaSector = "SELECT rstadetalle.vc_rstadet_nombre,rstadetalle.vc_rstadet_descripcion,clasificacion.vc_clasific_categoria,entidad.entvnombre,rstadetalle.vc_rstadet_direcurl,CASE WHEN b_direcurl_estado='1' THEN 'activo.png' ELSE 'inactivo.png' END "
		. "FROM idepcoor.gen_entidad entidad, idepcoor.gepr_ta_respuesta respuesta, idepcoor.gepr_ta_rstadetalle rstadetalle, idepcoor.gepr_ta_clasific clasificacion "
		. "WHERE (respuesta.entnid=entidad.entnid)and(rstadetalle.pk_id_rsta=respuesta.pk_id_rsta)and(rstadetalle.pk_id_clasific=clasificacion.pk_id_clasific)and(rstadetalle.ch_pregtip_codigo='Servicios REST')and(rstadetalle.ch_rstadet_accesolibre='SI')and(entidad.entvnombre='$entidad')"; 		
		$resultListaSector = mysqli_query($con,$sqlListaSector); 
		echo "<h3>$entidad</h3><div><table><thead><tr><th>Nombre del Servicio</th><th>Descripción</th><th>Tema</th><th>Proporcionado por</th><th>Estado</th><th>Dirección del Servicio</th><th>Acceso</th></tr></thead><tbody>";        		            
		while ($row = mysqli_fetch_row($resultListaSector))
		{
			if($row[5]=='activo.png'){$title="El servicio está operativo";$t="1";}else{$title="El servicio está temporalmente inactivo";$t="3";};	
			$item=base64_encode("REST".$row[4]);						
			echo "<tr><td>".$row[0]."</td><td>".$row[1]."</td><td>".$row[2]."</td><td>".$row[3]."</td>";
			echo "<td><img title='".$title."' src='/images/".$row[5]."'></td><td><div id='divContenido'><a href='http://".$httphost."/servicios/modalservicio.php?serv=$item' class='clsVentanaIFrame clsBoton' rel='Detalle de Servicio' on>Ver dirección</a></div></td>";					
			echo "<td>";						
			if($t==="1")
			{
				if (strpos($row[4],'MapServer')!==false)
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
	echo "<div id='tabs-2'><div id='accordion'>";        		
	$sqlClasificacion = "SELECT DISTINCT clasificacion.vc_clasific_categoria FROM idepcoor.gepr_ta_clasific clasificacion, idepcoor.gepr_ta_rstadetalle rstadetalle "
	. "WHERE (rstadetalle.pk_id_clasific=clasificacion.pk_id_clasific)and(rstadetalle.ch_pregtip_codigo='Servicios REST')";		
	$resultClasificacion = mysqli_query($con,$sqlClasificacion);                  
	while ($row = mysqli_fetch_row($resultClasificacion))            
	{
		$clasificacion=$row[0];		
		$sqlListaClasif = "SELECT rstadetalle.vc_rstadet_nombre,rstadetalle.vc_rstadet_descripcion,entidad.entvnombre,rstadetalle.vc_rstadet_direcurl,CASE WHEN b_direcurl_estado='1' THEN 'activo.png' ELSE 'inactivo.png' END "
			. "FROM idepcoor.gen_entidad entidad, idepcoor.gepr_ta_respuesta respuesta, idepcoor.gepr_ta_rstadetalle rstadetalle, idepcoor.gepr_ta_clasific clasificacion "
			. "WHERE (respuesta.entnid=entidad.entnid) and (rstadetalle.pk_id_rsta=respuesta.pk_id_rsta) and (rstadetalle.pk_id_clasific=clasificacion.pk_id_clasific)and(rstadetalle.ch_pregtip_codigo='Servicios REST')and(rstadetalle.ch_rstadet_accesolibre='SI')and(clasificacion.vc_clasific_categoria='$clasificacion')"; 	
		$resultListaClasif = mysqli_query($con,$sqlListaClasif); 
			
		
		echo "<h3>$clasificacion</h3><div><table><thead><tr><th>Nombre del Servicio</th><th>Descripción</th><th>Proporcionado por</th><th>Estado</th><th>Dirección del Servicio</th><th>Acceso</th></tr></thead><tbody>";        		          
		while ($row = mysqli_fetch_row($resultListaClasif))
		{
			if($row[4]=='activo.png'){$title="El servicio está operativo";$t="1";}else{$title="El servicio está temporalmente inactivo";$t="3";};	
			$item=base64_encode("REST".$row[3]);						
			echo "<tr><td>".$row[0]."</td><td>".$row[1]."</td><td>".$row[2]."</td>";
			echo "<td><img title='".$title."' src='/images/".$row[4]."'></td><td><div id='divContenido'><a href='http://".$httphost."/servicios/modalservicio.php?serv=$item' class='clsVentanaIFrame clsBoton' rel='Detalle de Servicio' on>Ver dirección</a></div></td>";					
			echo "<td>";						
			if($t==="1")
			{
				if (strpos($row[3],'MapServer')!==false)
				{ 
					echo "<div id='divContenido'><a href='http://mapas.geoidep.gob.pe/mapasperu/?config=viewer_wms&wmsuri=".$row[3]."&wmstitle=".$row[0]."&t=1' target='_blank' class='clsBoton' title='Web Service : ".$row[3]."' >Ver Mapa</a></div>"; 
				}else{
					echo "<div id='divContenido'><a href='" .$row[3]."' target='_blank' class='clsBoton'>Acceder</a></div>";		
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