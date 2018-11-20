<!DOCTYPE HTML>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Testing tukos forms</title>
 
        <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/dojo/1.14.1/dijit/themes/claro/claro.css" media="screen">
        <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/dojo/1.14.1/dojox/image/resources/Lightbox.css" media="screen">

    </head>
    <body class="claro">
		<b>The tukos form:</b><p>
<b>Introduction</b>:
<br>
<blockquote>
  This document demonstrates how tukos could be used to generate forms that can be used independently of tukos
  <br>
</blockquote>
<p>
  <b>Key challenges - Design and Implementation considerations</b>:
</p>
<blockquote>
  We start with a simple input element:
  <textarea style="height: 18px;">
  </textarea>
  and a clickable checkbox:&nbsp; <span class="clickablecheckbox" contenteditable="false" onclick="this.innerHTML = (this.innerHTML == '☐' ? '☑' : '☐')" style="cursor: pointer;">☑</span>
  <br> 
  <br>
</blockquote>
<b>Closing Considerations</b>:
<br>
<blockquote>
  &lt;Enter your text here&gt;
</blockquote>
<p>
Source: cet excellent <a href="http://www.velomath.fr/velo_equation.htm" onclick="parent.tukos.Pmg.gotoExternalUrl('http://www.velomath.fr/velo_equation.htm')" style="color:blue; cursor:pointer; text-decoration:underline;" target="_blank">article </a>qui &quot;met le vélo en équations&quot;:
<br>
<blockquote>
  <img alt="" src="data:image/gif;base64,R0lGODlh9AAsAHcAMSH+GlNvZnR3YXJlOiBNaWNyb3NvZnQgT2ZmaWNlACH5BAEAAAAALAEAAQDyACoAgAAAAAAAAAL/jI+py+0Po5y02oszBLz7D4biSJbmiabqyrbuOxrwTNf2jec6KO/+DwwKh4Ae8YhMKonGpfMJjZqaUiCic60dPltOt1qlgn8B7q3sxZrT46i4rUOr4/O6HL5843HyO9/b1LfnpDeoVfe3VoRoyOTXaCNIx8O2CHlUePlS9ngYgiapmUOVKcrCOflZ2Wm6WVnE2noa6/pqeUsr23JX6tLXS5K7G5f1BSjsg5xIifWbNcymHNMsc/WsG7P1+MwNa3Rdwm18yxjoIX2eHqn+ii7Ky+rHy26LIi8yD0/v3n62z4iNGb2B88pBA7jKmDmE4c6NY8jpXkKGvsKoCEVuVa2M/4o+Faw3paNAh6D+LSrpTwo6jNsohgTpMhA4bzMl4rvZT01Jfg1V4otoZyAujwlURVMY7GNFoTTHsUy382cEauA0TGCa5p5NqDNQAaS1UGpNkWQ1IvLqNaUbgS236uxKrmU2rCncloWV82yqtUHlyeXCEzBZsM7gHot3M6zeUWPE4WXaKXBHpEkZG74bMM+TXMBWSK73OfNeTFNC49yleJOYzoM9CqZrmXRp0XUXz3otdJtp0axpB91osPXuzL19Cyr+FerxfL5hICf+C2bPfSh7VG+uGnvtoadGSjruUvtc8S8xbjdpaflM8pXZBzPr2fsc88MDPucN/6L8aMHdnym4L8s1D/0nEzwGjuRfewkuqB2ADD7YhoMQTuiGVRZeiGGGGm7IIYcFAAA7">
  <br>
  <br>
  où:
  <br>
  <blockquote>
    f est le coefficient de frottement exprimé en %. Il varie de 0,75 à 1,5 en fonction du revêtement de la route (et de la nature et pression des pneus), en pratique 1%.
    <br>
    Cx est le coefficient de résistance à l’air. Il varie de 0.1 à 0.5. Il dépend avant tout de la position du coureur, puis de son gabarit, et enfin de l'aérodynamique du vélo.
    <br>
    W est le poids (cycliste + vélo) exprimé en kilo
    <br>
    &nbsp;p est la pente de la route exprimé en %: positif, la route monte, p= 4 par exemple négatif, la route descend, p= - 4 par exemple
    <br>
    V est la vitesse en km/h&nbsp;
    <br>
    Vr est la vitesse du vent sera positif si le vent vient de l’arrière et négatif s’il vient de face. L'équation n'est valable que si la vitesse relative du vent est inférieure à la vitesse du coureur.
    <br>
    P est la puissance en Watts
    <br>
  </blockquote>
</blockquote>
L'équation ci-dessus ne prend pas en compte le vent de travers non plus que l'effet des accélérations / décélérations. On trouvera aussi <a href="https://www.sci-sport.com/dossiers/methodes-d-evaluation-de-l-aerodynamisme-en-cyclisme-002.php" target="_blank">ici </a>un article très complet à orientation scientifique.
<br>
<br>
Quelques indications sur les ordres de grandeur et facteurs importants (glanés au fil des lectures):
<br>
<ul>
  <li>
    à partir de 20km/h, sur le plat, la résistance au vent l'emporte sur les forces de frottement.
  </li>
  <li>
    un écart de +/- 0,5 bars de pression de gonflage conduit rapidement à des erreurs de +/- 6,5 % sur les valeurs de coefficient de roulement (lu <a href="http://engineerstalk.mavic.com/evaluer-les-performances-dun-pneu-route-tests-resistance-au-roulement-dadherence/" onclick="parent.tukos.Pmg.gotoExternalUrl('http://engineerstalk.mavic.com/evaluer-les-performances-dun-pneu-route-tests-resistance-au-roulement-dadherence/')" style="color:blue; cursor:pointer; text-decoration:underline;" target="_blank">ici</a>). Il semble que gonfler à 7 bars est ce qu'il faut faire, et à cette pression, la variation devient non significative (?)
  </li>
  <li>
    Cx&nbsp; varie de 0,25 (cycliste seul &quot;debout&quot;) à 0,15(cycliste seul en position &quot;contre la montre&quot;), dépend de la taille et IMC du cycliste, et encore plus de l'aspiration (voir <a href="https://docplayer.fr/27930887-Le-deventement-en-cyclisme.html" target="_blank">ici</a>, plus 40% de déventement en suçant la roue d'un cycliste, 25% à 1 vélo de distance) et enfin de l'effet de peloton (80% et plus ?)
    <br>
  </li>
</ul>
<div>
  <br>
  <table border="1" cellpadding="0" cellspacing="0" class="tukosWorksheet" id="simulations" style="table-layout: fixed; width: 100%;">
    <tbody>
      <tr>
        <td contenteditable="false" id="tdid0_1542197008999" style="text-align: center; width: 5em;"><b><i>simulations</i></b></td><td contenteditable="false" id="tdid1_1542197008999" style="background-color: lightgrey; font-weight: bold; text-align: center;">A</td><td contenteditable="false" id="tdid2_1542197008999" style="background-color: lightgrey; font-weight: bold; text-align: center;">B</td><td contenteditable="false" id="tdid3_1542197008999" style="background-color: lightgrey; font-weight: bold; text-align: center;">C</td><td contenteditable="false" id="tdid4_1542197008999" style="background-color: lightgrey; font-weight: bold; text-align: center;">D</td><td contenteditable="false" id="tdid5_1542197008999" style="background-color: lightgrey; font-weight: bold; text-align: center;">E</td><td contenteditable="false" id="tdid6_1542197008999" style="background-color: lightgrey; font-weight: bold; text-align: center;">F</td><td contenteditable="false" id="tdid7_1542197008999" style="background-color: lightgrey; font-weight: bold; text-align: center;">G</td><td contenteditable="false" id="tdid8_1542197008999" style="background-color: lightgrey; font-weight: bold; text-align: center;">H</td><td contenteditable="false" id="tdid9_1542197008999" style="background-color: lightgrey; font-weight: bold; text-align: center;">I</td><td contenteditable="false" id="tdid10_1542197008999" style="background-color: lightgrey; font-weight: bold; text-align: center;">J</td>
      </tr>
      <tr>
        <td contenteditable="false" id="tdid11_1542197008999" style="background-color: lightgrey; font-weight: bold; text-align: center;">1</td><td id="tdid12_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="font-weight: bold; text-align: center;">
        <div class="tukosExpression" id="e_simulations!A1" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!A1">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;">Paramètres </span>
        </div>
        </td><td id="tdid13_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="font-weight: bold; text-align: center;">
        <div class="tukosExpression" id="e_simulations!C1" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!C1">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;">Référence</span>
        </div>
        </td><td id="tdid14_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="font-weight: bold; text-align: center;">
        <div class="tukosExpression" id="e_simulations!C1" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!C1">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;">V: +5 km/h</span>
        </div>
        </td><td id="tdid15_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="font-weight: bold; text-align: center;">
        <div class="tukosExpression" id="e_simulations!D1" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!D1">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;">Vr: -10 km/h</span>
        </div>
        </td><td id="tdid16_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="font-weight: bold; text-align: center;">
        <div class="tukosExpression" id="e_simulations!E1" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!E1">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;"> </span>
        </div>
        </td><td id="tdid17_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="font-weight: bold; text-align: center;">
        <div class="tukosExpression" id="e_simulations!F1" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!F1">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;"> </span>
        </div>
        </td><td id="tdid18_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="font-weight: bold; text-align: center;">
        <div class="tukosExpression" id="e_simulations!G1" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!G1">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;"> </span>
        </div>
        </td><td id="tdid19_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="font-weight: bold; text-align: center;">
        <div class="tukosExpression" id="e_simulations!H1" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!H1">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;"> </span>
        </div>
        </td><td id="tdid20_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="font-weight: bold; text-align: center;">
        <div class="tukosExpression" id="e_simulations!I1" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!I1">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;"> </span>
        </div>
        </td><td id="tdid21_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="font-weight: bold; text-align: center;">
        <div class="tukosExpression" id="e_simulations!J1" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!J1">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;"> </span>
        </div>
        </td>
      </tr>
      <tr>
        <td contenteditable="false" id="tdid22_1542197008999" style="background-color: lightgrey; font-weight: bold; text-align: center;">2</td><td id="tdid23_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!A2" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!A2">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;">f (%) </span>
        </div>
        </td><td id="tdid24_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!B2" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!B2">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;">1 </span>
        </div>
        </td><td id="tdid25_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!C2" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!C2">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;">1 </span>
        </div>
        </td><td id="tdid26_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!D2" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!D2">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;">1 </span>
        </div>
        </td><td id="tdid27_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!E2" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!E2">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;"> </span>
        </div>
        </td><td id="tdid28_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!F2" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!F2">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;"> </span>
        </div>
        </td><td id="tdid29_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!G2" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!G2">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;"> </span>
        </div>
        </td><td id="tdid30_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!H2" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!H2">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;"> </span>
        </div>
        </td><td id="tdid31_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!I2" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!I2">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;"> </span>
        </div>
        </td><td id="tdid32_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!J2" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!J2">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;"> </span>
        </div>
        </td>
      </tr>
      <tr>
        <td contenteditable="false" id="tdid33_1542197008999" style="background-color: lightgrey; font-weight: bold; text-align: center;">3</td><td id="tdid34_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!A3" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!A3">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;">p (%) </span>
        </div>
        </td><td id="tdid35_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!B3" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!B3">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;">0 </span>
        </div>
        </td><td id="tdid36_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!C3" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!C3">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;">0 </span>
        </div>
        </td><td id="tdid37_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!D3" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!D3">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;">0 </span>
        </div>
        </td><td id="tdid38_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!E3" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!E3">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;"> </span>
        </div>
        </td><td id="tdid39_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!F3" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!F3">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;"> </span>
        </div>
        </td><td id="tdid40_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!G3" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!G3">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;"> </span>
        </div>
        </td><td id="tdid41_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!H3" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!H3">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;"> </span>
        </div>
        </td><td id="tdid42_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!I3" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!I3">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;"> </span>
        </div>
        </td><td id="tdid43_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!J3" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!J3">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;"> </span>
        </div>
        </td>
      </tr>
      <tr>
        <td contenteditable="false" id="tdid44_1542197008999" style="background-color: lightgrey; font-weight: bold; text-align: center;">4</td><td id="tdid45_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!A4" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!A4">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;">W (Kg) </span>
        </div>
        </td><td id="tdid46_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!B4" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!B4">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;">80 </span>
        </div>
        </td><td id="tdid47_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!C4" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!C4">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;">80 </span>
        </div>
        </td><td id="tdid48_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!D4" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!D4">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;">80 </span>
        </div>
        </td><td id="tdid49_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!E4" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!E4">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;"> </span>
        </div>
        </td><td id="tdid50_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!F4" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!F4">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;"> </span>
        </div>
        </td><td id="tdid51_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!G4" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!G4">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;"> </span>
        </div>
        </td><td id="tdid52_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!H4" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!H4">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;"> </span>
        </div>
        </td><td id="tdid53_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!I4" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!I4">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;"> </span>
        </div>
        </td><td id="tdid54_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!J4" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!J4">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;"> </span>
        </div>
        </td>
      </tr>
      <tr>
        <td contenteditable="false" id="tdid55_1542197008999" style="background-color: lightgrey; font-weight: bold; text-align: center;">5</td><td id="tdid56_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!A5" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!A5">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;">Cx</span>
        </div>
        </td><td id="tdid57_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!B5" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!B5">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;">0.2 </span>
        </div>
        </td><td id="tdid58_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!C5" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!C5">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;">0.2 </span>
        </div>
        </td><td id="tdid59_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!D5" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!D5">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;">0.2 </span>
        </div>
        </td><td id="tdid60_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!E5" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!E5">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;"> </span>
        </div>
        </td><td id="tdid61_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!F5" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!F5">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;"> </span>
        </div>
        </td><td id="tdid62_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!G5" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!G5">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;"> </span>
        </div>
        </td><td id="tdid63_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!H5" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!H5">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;"> </span>
        </div>
        </td><td id="tdid64_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!I5" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!I5">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;"> </span>
        </div>
        </td><td id="tdid65_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!J5" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!J5">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;"> </span>
        </div>
        </td>
      </tr>
      <tr>
        <td contenteditable="false" id="tdid66_1542197008999" style="background-color: lightgrey; font-weight: bold; text-align: center;">6</td><td id="tdid67_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!A6" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!A6">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;">V (Km/h) </span>
        </div>
        </td><td id="tdid68_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!B6" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!B6">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;">25</span>
        </div>
        </td><td id="tdid69_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!C6" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!C6">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;">30</span>
        </div>
        </td><td id="tdid70_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!D6" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!D6">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;">25</span>
        </div>
        </td><td id="tdid71_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!E6" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!E6">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;"> </span>
        </div>
        </td><td id="tdid72_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!F6" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!F6">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;"> </span>
        </div>
        </td><td id="tdid73_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!G6" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!G6">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;"> </span>
        </div>
        </td><td id="tdid74_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!H6" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!H6">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;"> </span>
        </div>
        </td><td id="tdid75_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!I6" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!I6">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;"> </span>
        </div>
        </td><td id="tdid76_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!J6" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!J6">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;"> </span>
        </div>
        </td>
      </tr>
      <tr>
        <td contenteditable="false" id="tdid77_1542197008999" style="background-color: lightgrey; font-weight: bold; text-align: center;">7</td><td id="tdid78_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!A7" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!A7">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;">Vr (Km/h) </span>
        </div>
        </td><td id="tdid79_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!B7" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!B7">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;">0</span>
        </div>
        </td><td id="tdid80_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!C7" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!C7">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;">0</span>
        </div>
        </td><td id="tdid81_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!D7" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!D7">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;">(-10)</span>
        </div>
        </td><td id="tdid82_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!E7" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!E7">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;"> </span>
        </div>
        </td><td id="tdid83_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!F7" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!F7">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;"> </span>
        </div>
        </td><td id="tdid84_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!G7" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!G7">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;"> </span>
        </div>
        </td><td id="tdid85_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!H7" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!H7">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;"> </span>
        </div>
        </td><td id="tdid86_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!I7" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!I7">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;"> </span>
        </div>
        </td><td id="tdid87_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!J7" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!J7">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;"> </span>
        </div>
        </td>
      </tr>
      <tr>
        <td contenteditable="false" id="tdid88_1542197008999" style="background-color: lightgrey; font-weight: bold; text-align: center;">8</td><td id="tdid89_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!A8" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!A8">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;"> </span>
        </div>
        </td><td id="tdid90_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!B8" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!B8">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;"> </span>
        </div>
        </td><td id="tdid91_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!C8" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!C8">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;"> </span>
        </div>
        </td><td id="tdid92_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!D8" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!D8">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;"> </span>
        </div>
        </td><td id="tdid93_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!E8" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!E8">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;"> </span>
        </div>
        </td><td id="tdid94_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!F8" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!F8">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;"> </span>
        </div>
        </td><td id="tdid95_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!G8" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!G8">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;"> </span>
        </div>
        </td><td id="tdid96_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!H8" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!H8">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;"> </span>
        </div>
        </td><td id="tdid97_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!I8" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!I8">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;"> </span>
        </div>
        </td><td id="tdid98_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!J8" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!J8">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;"> </span>
        </div>
        </td>
      </tr>
      <tr>
        <td contenteditable="false" id="tdid99_1542197008999" style="background-color: lightgrey; font-weight: bold; text-align: center;">9</td><td id="tdid100_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!A9" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!A9">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;">P frottement (W)</span>
        </div>
        </td><td id="tdid101_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression tukosFormula e_simulations!B2 e_simulations!B4 e_simulations!B6" data-formulacache="56" id="e_simulations!B9" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!B9">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
            =(B2*B4*B6/36).toFixed(0)
          </textarea>
          <span style="display: inline;">56</span>
        </div>
        </td><td id="tdid102_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression tukosFormula e_simulations!C2 e_simulations!C4 e_simulations!C6" data-formulacache="67" id="e_simulations!C9" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!C9">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
            =(C2*C4*C6/36).toFixed(0)
          </textarea>
          <span style="display: inline;">67</span>
        </div>
        </td><td id="tdid103_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression tukosFormula e_simulations!D2 e_simulations!D4 e_simulations!D6" data-formulacache="56" id="e_simulations!D9" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!D9">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
            =(D2*D4*D6/36).toFixed(0)
          </textarea>
          <span style="display: inline;">56</span>
        </div>
        </td><td id="tdid104_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!E9" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!E9">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;"> </span>
        </div>
        </td><td id="tdid105_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!F9" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!F9">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;"> </span>
        </div>
        </td><td id="tdid106_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!G9" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!G9">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;"> </span>
        </div>
        </td><td id="tdid107_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!H9" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!H9">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;"> </span>
        </div>
        </td><td id="tdid108_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!I9" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!I9">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;"> </span>
        </div>
        </td><td id="tdid109_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!J9" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!J9">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;"> </span>
        </div>
        </td>
      </tr>
      <tr>
        <td contenteditable="false" id="tdid110_1542197008999" style="background-color: lightgrey; font-weight: bold; text-align: center;">10</td><td id="tdid111_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!A10" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!A10">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;">P pente (W) </span>
        </div>
        </td><td id="tdid112_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression tukosFormula e_simulations!B3 e_simulations!B4 e_simulations!B6" data-formulacache="0" id="e_simulations!B10" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!B10">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
            =(B3*B4*B6/36).toFixed(0)
          </textarea>
          <span style="display: inline;">0</span>
        </div>
        </td><td id="tdid113_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression tukosFormula e_simulations!C3 e_simulations!C4 e_simulations!C6" data-formulacache="0" id="e_simulations!C10" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!C10">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
            =(C3*C4*C6/36).toFixed(0)
          </textarea>
          <span style="display: inline;">0</span>
        </div>
        </td><td id="tdid114_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression tukosFormula e_simulations!D3 e_simulations!D4 e_simulations!D6" data-formulacache="0" id="e_simulations!D10" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!D10">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
            =(D3*D4*D6/36).toFixed(0)
          </textarea>
          <span style="display: inline;">0</span>
        </div>
        </td><td id="tdid115_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!E10" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!E10">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;"> </span>
        </div>
        </td><td id="tdid116_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!F10" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!F10">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;"> </span>
        </div>
        </td><td id="tdid117_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!G10" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!G10">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;"> </span>
        </div>
        </td><td id="tdid118_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!H10" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!H10">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;"> </span>
        </div>
        </td><td id="tdid119_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!I10" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!I10">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;"> </span>
        </div>
        </td><td id="tdid120_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!J10" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!J10">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;"> </span>
        </div>
        </td>
      </tr>
      <tr>
        <td contenteditable="false" id="tdid121_1542197008999" style="background-color: lightgrey; font-weight: bold; text-align: center;">11</td><td id="tdid122_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!A11" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!A11">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;">P vent (W) </span>
        </div>
        </td><td id="tdid123_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression tukosFormula e_simulations!B5 e_simulations!B6 e_simulations!B7" data-formulacache="67" id="e_simulations!B11" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!B11">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
            =(250/11664*B5*B6*(B6-B7)**2).toFixed(0)
          </textarea>
          <span style="display: inline;">67</span>
        </div>
        </td><td id="tdid124_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression tukosFormula e_simulations!C5 e_simulations!C6 e_simulations!C7" data-formulacache="116" id="e_simulations!C11" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!C11">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
            =(250/11664*C5*C6*(C6-C7)**2).toFixed(0)
          </textarea>
          <span style="display: inline;">116</span>
        </div>
        </td><td id="tdid125_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression tukosFormula e_simulations!D5 e_simulations!D6 e_simulations!D7" data-formulacache="131" id="e_simulations!D11" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!D11">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
            =(250/11664*D5*D6*(D6-D7)**2).toFixed(0)
          </textarea>
          <span style="display: inline;">131</span>
        </div>
        </td><td id="tdid126_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!E11" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!E11">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;"> </span>
        </div>
        </td><td id="tdid127_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!F11" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!F11">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;"> </span>
        </div>
        </td><td id="tdid128_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!G11" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!G11">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;"> </span>
        </div>
        </td><td id="tdid129_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!H11" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!H11">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;"> </span>
        </div>
        </td><td id="tdid130_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!I11" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!I11">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;"> </span>
        </div>
        </td><td id="tdid131_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!J11" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!J11">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;"> </span>
        </div>
        </td>
      </tr>
      <tr>
        <td contenteditable="false" id="tdid132_1542197008999" style="background-color: lightgrey; font-weight: bold; text-align: center;">12</td><td id="tdid133_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!A12" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!A12">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;">P Totale (W) </span>
        </div>
        </td><td id="tdid134_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression tukosFormula e_simulations!B9 e_simulations!B10 e_simulations!B11" data-formulacache="123" id="e_simulations!B12" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!B12">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
            =B9+B10+B11
          </textarea>
          <span style="display: inline;">123</span>
        </div>
        </td><td id="tdid135_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression tukosFormula e_simulations!C9 e_simulations!C10 e_simulations!C11" data-formulacache="183" id="e_simulations!C12" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!C12">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
            =C9+C10+C11
          </textarea>
          <span style="display: inline;">183</span>
        </div>
        </td><td id="tdid136_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression tukosFormula e_simulations!D9 e_simulations!D10 e_simulations!D11" data-formulacache="187" id="e_simulations!D12" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!D12">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
            =D9+D10+D11
          </textarea>
          <span style="display: inline;">187</span>
        </div>
        </td><td id="tdid137_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!E12" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!E12">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;"> </span>
        </div>
        </td><td id="tdid138_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!F12" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!F12">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;"> </span>
        </div>
        </td><td id="tdid139_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!G12" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!G12">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;"> </span>
        </div>
        </td><td id="tdid140_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!H12" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!H12">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;"> </span>
        </div>
        </td><td id="tdid141_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!I12" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!I12">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;"> </span>
        </div>
        </td><td id="tdid142_1542197008999" onclick="parent.tukos.onTdClick(this);" ondblclick="parent.tukos.onTdDblClick(this);" style="text-align: center;">
        <div class="tukosExpression" id="e_simulations!J12" onclick="parent.tukos.onExpClick(this);" style="display: inline;" title="simulations!J12">
          <textarea onblur="parent.tukos.onExpBlur(this);" onclick="event.stopPropagation();" style="display: none; height: 18px;">
          </textarea>
          <span style="display: inline;"> </span>
        </div>
        </td>
      </tr>
    </tbody>
  </table>
  <br>
  <br>
  <br>
  <br>
</div>
<ul>
</ul>

        <script>var dojoConfig ={
                    baseUrl: "", isDebug: true, async: true, locale: "en-en",
                    //packages: [{"name": "dojo", "location": "dojo-release-1.12.2-src/dojo"},   {"name": "dijit", "location": "dojo-release-1.12.2-src/dijit"},  
                    //           {"name": "dojox", "location": "dojo-release-1.12.2-src/dojox"},{"name": "tukos", "location": "tukosenv/src/tukos"}
                    //],
                    packages: [{"name": "tukos", "location": "tukosenv/src/tukos"}]
                };
        </script>
        <script src="https://ajax.googleapis.com/ajax/libs/dojo/1.14.1/dojo/dojo.js"></script>
		<script>
		    require(['tukos/expressions', 'tukos/PageManager'], function (expressions, Pmg) {
				tukos = {Pmg: Pmg};
	    	    tukos.onTdClick = function(td){
	    	    	expressions.onClick(td.children[0]);
	    	    };
	    	    tukos.onTdDblClick = function(td){
	    	    	expressions.onClick(td.children[0]);
	    	    };
	    	    tukos.onExpClick = function(expression){
	    	    	expressions.onClick(expression);
	    	    };
	    	    tukos.onExpBlur = function(expression){
	    	    	expressions.onBlur(expression);
	    	    };
	    	});
		</script>

    </body>
</html>
