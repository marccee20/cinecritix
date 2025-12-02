
    <!-- footer -->
    <footer>
      <div class="social-links">
        <a href="#"><i class="fab fa-facebook-f"></i></a>
        <a href="#"><i class="fab fa-twitter"></i></a>
        <a href="#"><i class="fab fa-instagram"></i></a>
        <a href="#"><i class="fab fa-pinterest"></i></a>
      </div>
      <span>cinecritx</span>
    </footer>

    <script src="assets/js/search.js"></script>
    <script>
      // Normaliza enlaces existentes al cargar
      (function() {
        try {
          document.querySelectorAll('a[href="#peliculas"]').forEach(a => {
            a.setAttribute('href', '/cinecritix/peliculas.php');
          });
        } catch (_) {}
      })();

      // Captura el click en fase de captura para evitar el scroll por ancla
      document.addEventListener('click', function(e) {
        const link = e.target.closest('a');
        if (!link) return;
        const href = (link.getAttribute('href') || '').trim();
        const text = (link.textContent || '').trim().toLowerCase();
        if (href === '#peliculas' || /#peliculas$/i.test(href) || text === 'películas') {
          e.preventDefault();
          e.stopPropagation();
          window.location.assign('/cinecritix/peliculas.php');
        }
      }, true);
    </script>
  </body>
</html>
