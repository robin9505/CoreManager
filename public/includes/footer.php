<footer class="main-footer">
    <div class="footer-container">
        
        
        <p class="footer-text">&copy; <?php echo date('Y'); ?> <strong>CoreManager</strong>. Todos los derechos reservados.</p>
        <a href="logout.php" class="btn-logout">🚪 Cerrar Sesión</a>
    </div>
</footer>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

<?php if (isset($loadDataTables) && $loadDataTables): ?>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<?php endif; ?>

<?php if (isset($extraJS)) echo $extraJS; ?>