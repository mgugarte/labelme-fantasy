/**
 * Custom Dropdown - Reemplazo de <select> nativo
 * Convierte automáticamente todos los <select> en dropdowns personalizados
 */

class CustomDropdown {
  constructor(selectElement) {
    this.select = selectElement;
    this.select.classList.add('custom-select');
    this.createDropdown();
    this.attachEvents();
  }

  createDropdown() {
    // Crear contenedor
    const wrapper = document.createElement('div');
    wrapper.className = 'custom-dropdown';

    // Crear botón toggle
    const toggle = document.createElement('button');
    toggle.type = 'button';
    toggle.className = 'custom-dropdown-toggle';
    toggle.textContent = this.select.options[this.select.selectedIndex].text;

    // Crear menú de opciones
    const menu = document.createElement('div');
    menu.className = 'custom-dropdown-menu';

    // Añadir opciones
    Array.from(this.select.options).forEach((option, index) => {
      const item = document.createElement('div');
      item.className = 'custom-dropdown-option';
      item.textContent = option.text;
      item.dataset.value = option.value;
      item.dataset.index = index;

      if (option.selected) {
        item.classList.add('selected');
      }

      menu.appendChild(item);
    });

    // Ensamblar
    wrapper.appendChild(toggle);
    wrapper.appendChild(menu);
    this.select.parentNode.insertBefore(wrapper, this.select);

    // Guardar referencias
    this.wrapper = wrapper;
    this.toggle = toggle;
    this.menu = menu;
  }

  attachEvents() {
    // Toggle al hacer click en el botón
    this.toggle.addEventListener('click', (e) => {
      e.stopPropagation();
      this.toggleMenu();
    });

    // Seleccionar opción
    this.menu.addEventListener('click', (e) => {
      if (e.target.classList.contains('custom-dropdown-option')) {
        this.selectOption(e.target);
      }
    });

    // Cerrar al hacer click fuera
    document.addEventListener('click', (e) => {
      if (!this.wrapper.contains(e.target)) {
        this.closeMenu();
      }
    });

    // Cerrar con ESC
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && this.wrapper.classList.contains('active')) {
        this.closeMenu();
      }
    });

    // Sincronizar cuando cambia el select original
    this.select.addEventListener('change', () => {
      this.updateToggleText();
    });
  }

  toggleMenu() {
    const isActive = this.wrapper.classList.contains('active');
    
    // Cerrar otros dropdowns
    document.querySelectorAll('.custom-dropdown.active').forEach(dropdown => {
      if (dropdown !== this.wrapper) {
        dropdown.classList.remove('active');
      }
    });

    this.wrapper.classList.toggle('active', !isActive);
  }

  closeMenu() {
    this.wrapper.classList.remove('active');
  }

  selectOption(optionElement) {
    const index = parseInt(optionElement.dataset.index);
    
    // Actualizar select original
    this.select.selectedIndex = index;
    
    // Disparar evento change para que los filtros funcionen
    const event = new Event('change', { bubbles: true });
    this.select.dispatchEvent(event);

    // Actualizar UI
    this.menu.querySelectorAll('.custom-dropdown-option').forEach(opt => {
      opt.classList.remove('selected');
    });
    optionElement.classList.add('selected');

    this.updateToggleText();
    this.closeMenu();
  }

  updateToggleText() {
    this.toggle.textContent = this.select.options[this.select.selectedIndex].text;
  }
}

// Inicializar todos los dropdowns cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
  // Convertir todos los <select> dentro de .filtro-grupo
  const selects = document.querySelectorAll('.filtro-grupo select');
  
  selects.forEach(select => {
    new CustomDropdown(select);
  });
});
