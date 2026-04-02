<?php
// templates/header.php
if (!isset($pageTitle)) {
    $pageTitle = 'Mensa';
}
?>
<!DOCTYPE html>
<html>
<head>
<title><?php echo h($pageTitle); ?></title>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="./style.css">
<?php
// Add w3.css if not in style.css or specifically required (the original files loaded it from w3schools)
?>
<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
<link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<style>
body, h1,h2,h3,h4,h5,h6 {font-family: "Arial", sans-serif}
.w3-row-padding img {margin-bottom: 12px}
/* Set the width of the sidebar to 120px */
.w3-sidebar {width: 120px;background: #222;}
/* Add a left margin to the "page content" that matches the width of the sidebar (120px) */
#main, #main2 {margin-left: 120px}
/* Remove margins from "page content" on small screens */
@media only screen and (max-width: 600px) {#main, #main2 {margin-left: 0}}
input[type='radio'] {
     transform: scale(2.5);
}
td select.w3-input {
    max-width: 500px; 
    min-width: 150px;
    text-overflow: ellipsis;
}
</style>
</head>
<body class="w3-black">

<!-- Icon Bar (Sidebar - hidden on small screens) -->
<nav class="w3-sidebar w3-bar-block w3-small w3-hide-small w3-center">
  <!-- Avatar image in top left corner -->
  <img src="images/croissant.jpg" style="height:120px">
  
  <?php if (isset($sidebarHtml)): ?>
      <?php echo $sidebarHtml; ?>
  <?php endif; ?>
</nav>

<!-- Navbar on small screens (Hidden on medium and large screens) -->
<div class="w3-top w3-hide-large w3-hide-medium" id="myNavbar">
  <div class="w3-bar w3-black w3-opacity w3-hover-opacity-off w3-center w3-small">
    <?php if (isset($navbarSmallHtml)): ?>
        <?php echo $navbarSmallHtml; ?>
    <?php endif; ?>
  </div>
</div>

<!-- Page Content -->
<div class="w3-padding-large" id="main">
