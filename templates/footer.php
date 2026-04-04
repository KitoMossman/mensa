<?php
// templates/footer.php
?>
  <!-- Global Footer & Impressum -->
  <footer class="w3-padding-64">
    <div class="page-container">
      <?php include __DIR__ . '/../impressum.php'; ?>
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
