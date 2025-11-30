// Autocompletado para el buscador
const searchInput = document.getElementById('search-input');
const suggestionsList = document.getElementById('suggestions-list');
let debounceTimer;

function getSuggestions(query) {
  if (query.length < 2) {
    suggestionsList.innerHTML = '';
    return;
  }

  fetch('api_buscar.php?q=' + encodeURIComponent(query))
    .then(response => response.json())
    .then(data => {
      displaySuggestions(data);
    })
    .catch(error => console.error('Error:', error));
}

function displaySuggestions(results) {
  suggestionsList.innerHTML = '';

  if (results.length === 0) {
    suggestionsList.innerHTML = '<div class="suggestion-item no-results">No se encontraron películas</div>';
    return;
  }

  results.forEach(movie => {
    const item = document.createElement('div');
    item.className = 'suggestion-item';
    
    // Crear la miniatura
    let imgHtml = '';
    if (movie.imagen) {
      imgHtml = `<img src="data:image/jpeg;base64,${movie.imagen}" class="suggestion-thumb" alt="${movie.nombre}">`;
    } else {
      imgHtml = '<div class="suggestion-thumb" style="background:#eee;"></div>';
    }

    // Crear el HTML del item
    item.innerHTML = `
      <a href="info.php?id=${encodeURIComponent(movie.id_peliculas)}" style="display:flex; gap:10px; align-items:center; text-decoration:none; color:inherit;">
        ${imgHtml}
        <span>${movie.nombre}</span>
      </a>
    `;

    suggestionsList.appendChild(item);
  });
}

searchInput.addEventListener('input', function() {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    getSuggestions(this.value);
  }, 300);
});

// Cerrar sugerencias al hacer clic fuera
document.addEventListener('click', function(e) {
  if (e.target !== searchInput) {
    suggestionsList.innerHTML = '';
  }
});

// Enviar formulario con Enter
searchInput.addEventListener('keydown', function(e) {
  if (e.key === 'Enter' && this.value.length > 0) {
    document.getElementById('search-form').submit();
  }
});
