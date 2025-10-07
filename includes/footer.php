    <!-- Footer -->
     <footer class="bg-gray-800 text-white py-8 mt-12 no-print">
        <div class="container mx-auto px-6 text-center">
            <p>&copy; <?php echo date('Y'); ?> <a href="https://www.chrisandemmashow.com" target="_blank" class="hover:underline">Chris and Emma</a>. All rights reserved.</p>
        </div>
    </footer>
    
    <script>
        // JavaScript for mobile menu toggle
        // Placed in the footer so it runs after the menu elements have been loaded.
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');
        
        if (mobileMenuButton && mobileMenu) {
            mobileMenuButton.addEventListener('click', () => {
                mobileMenu.classList.toggle('hidden');
            });
        }
    </script>

</body>
</html>
