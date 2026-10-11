<?php
/**
 * CraveHub - Admin Footer Template
 */
?>
        </div> <!-- End of .admin-body -->
    </div> <!-- End of .admin-main -->
</div> <!-- End of .admin-layout -->

<!-- Toast Notification Container -->
<div id="toast-container" class="toast-container"></div>

<!-- Admin JavaScript Scripts -->
<script src="<?php echo baseUrl('assets/js/main.js'); ?>"></script>
<script src="<?php echo baseUrl('assets/js/admin.js'); ?>"></script>

<?php if (isset($adminExtraScripts)): ?>
    <?php foreach ($adminExtraScripts as $s): ?>
        <script src="<?php echo $s; ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>

</body>
</html>
