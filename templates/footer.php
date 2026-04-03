<?php
// templates/footer.php
?>
  <!-- Global Footer & Impressum -->
  <footer class="w3-padding-64">
    <div class="page-container">
      <?php include __DIR__ . '/../impressum.php'; ?>
      
      <hr class="w3-opacity">
      <p class="w3-medium w3-text-grey" style="text-align:center;">
        Powered by <a href="https://www.w3schools.com/w3css/default.asp" target="_blank" class="w3-hover-text-green">w3.css</a>
      </p>
    </div>
  </footer>

<!-- END PAGE CONTENT -->
</div>

<?php 
// Optional closing for extra divs
if (isset($closeExtraDiv) && $closeExtraDiv === true): ?>
</div>
<?php endif; ?>

</body>
</html>
