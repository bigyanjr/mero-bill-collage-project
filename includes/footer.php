        </main>
    </div>
</div>
<?php 
// Include Chatbot widget here if needed 
// Note: We remove the 'footer' container because the layout is now 100vh app style. 
// We can assume the chatbot widget code is still valid if it's purely absolute/fixed positioned
// But we need to make sure the div structure from navbar is closed.
?>
<!-- Chatbot Widget -->
<?php if (function_exists('is_logged_in') && is_logged_in()): ?>
    <?php include_once 'chat_widget.php'; ?>
<?php endif; ?>

<script src="../assets/js/app.js"></script>
</body>
</html>
