</main> <!-- Penutup tag <main> dari header -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Tambahkan JavaScript spesifik halaman di sini jika perlu -->
<?php if (isset($page_scripts)): ?>
    <?php echo $page_scripts; ?>
<?php endif; ?>

</body>
</html>