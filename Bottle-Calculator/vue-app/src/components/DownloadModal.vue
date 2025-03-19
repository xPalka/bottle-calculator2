<template>
  <div v-if="visible" class="modal-overlay bottle-calculator-modal" @click.self="closeModal">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Pobierz obraz</h5>
      </div>
      <div class="modal-body">

        <form v-if="!successMessage">
          <div class="bottle-download-name-container">
            <input type="email" id="email" v-model="email" class="bottle-download-name" placeholder="e-mail" required />
          </div>
          <div style="margin: 12px 0;" class="form-check">
            <input type="checkbox" id="acceptTerms" v-model="acceptTerms" class="form-check-input" required />
            <label for="acceptTerms" class="form-check-label">Akceptuję regulamin, który zawiera </label>
            <a @click="toggleTerms" style="color: var(--bottle-calc-button-bgcolor);" class="terms-body-text expand-btn">{{ termsExpanded ? '[ukryj]' : '[...]' }}</a>
            <div v-show="termsExpanded" class="terms-body terms-body-text">
              <p>ipsum dolor sit amet, consectetur adipiscing elit. Quisque id diam sit amet odio tempus auctor. Aliquam erat volutpat. Duis nec nunc sed neque bibendum cursus. Integer ac eros vel orci tempus vestibulum et ac lorem. Aenean auctor justo non lorem vehicula, ac tincidunt libero faucibus. Donec pharetra turpis a libero facilisis, ut malesuada ipsum ultricies.</p>
              <p>Nam eget risus at magna dictum bibendum. Vivamus ut sapien ac quam condimentum suscipit. Donec consectetur ex a risus volutpat, a pharetra nulla cursus. Cras at lacus ac felis scelerisque facilisis. Integer et volutpat tortor. Proin malesuada velit sed mauris lacinia, eget convallis odio sodales. Nullam viverra, arcu in pharetra dapibus, metus sem tincidunt orci, sed vehicula erat nisi sed ligula.</p>
            </div>
          </div>
          <div style="display: flex; justify-content: center; align-content: center;">
            <button style="width:50%;" type="submit" @click="handleFormSubmit">Pobierz obraz</button>
            <button style="width:50%;" type="button" @click="closeModal">Anuluj</button>
          </div>
        </form>
        <div v-else>
          <p>{{ successMessage }}</p>
          <div style="display:flex; align-content: end; justify-content: end">
            <button style="width:50%;" type="button" @click="closeModal">Zamknij</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    visible: Boolean,
  },
  data() {
    return {
      email: '',
      acceptTerms: false,
      termsExpanded: false,
      successMessage: '',
    };
  },
  methods: {
    handleFormSubmit() {
      if (!this.email || !this.acceptTerms) {
        // alert('Proszę podać poprawny adres email i zaakceptować regulamin.');
        return;
      }
      const emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
      if (!emailPattern.test(this.email)) {
        return;
      }

      this.successMessage = 'Na podany e-mail zostało wysłane zdjęcie z utworzoną butelką z etykietą.';

      this.$emit('submit', this.email);
    },
    closeModal() {
      this.email = '';
      this.acceptTerms = false;
      this.successMessage = '';
      this.$emit('close');
    },
    toggleTerms() {
      this.termsExpanded = !this.termsExpanded;
    }
  },
};
</script>


<style scoped>
.terms-body-text, p {
  font-family: Arial, serif;
}

button[type='button'] {
  background-color: #6c757d;
  color: white;
  border: none;
  padding: 10px 20px;
  cursor: pointer;
  margin-top: 10px;
  min-width: 135px;
}
button[type='button']:hover {
  background-color: #5a6268;
  color: white;
}
button[type='button']:disabled {
  background-color: #6c757d;
  cursor: default;
  opacity: .65;
  color: white;
}

button[type='submit'] {
  background-color: var(--bottle-calc-button-bgcolor);
  color: var(--bottle-calc-button-textcolor);
  border: none;
  padding: 10px 20px;
  cursor: pointer;
  margin-top: 10px;
  min-width: 135px;
}
button[type='submit']:hover {
  background-color: var(--bottle-calc-button-bgcolor-hover);
  color: var(--bottle-calc-button-textcolor-hover);
}
button[type='submit']:disabled {
  background-color: #6c757d;
  cursor: default;
  opacity: .65;
  color: white;
}

.bottle-download-name {
  width: 100%;
  margin-top: 2px;
}
.bottle-download-name-container {
  width: 100%;
  display: flex;
  margin-top: 2px;
}

input[type='email'] {
  font-family: Arial, sans-serif;
  font-size: 14px;
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 0;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  transition: border-color 0.3s ease, box-shadow 0.3s ease;
  width: 100%;
}

input[type='email']:focus {
  border-color: var(--bottle-calc-button-bgcolor-hover);
  outline: none;
}

.bottle-calculator-modal.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1050;
}

.bottle-calculator-modal .modal-content {
  background-color: #fff;
  width: 100%;
  max-width: 500px;
  padding: 16px;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.25);
  transition: opacity 0.3s ease, visibility 0.3s ease;
}

.bottle-calculator-modal .modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid #dee2e6;
  margin-bottom: 0;
}

.bottle-calculator-modal .modal-title {
  font-size: 1.25rem;
  font-family: Arial, serif;
  margin: 0 0 8px 0;
}

.bottle-calculator-modal .modal-body {
  padding: 1rem;
}



.bottle-calculator-modal .form-check-input {
  margin: 0;
  appearance: none; /* Ukrywa domyślny wygląd checkboxa */
  width: 1.2rem;
  height: 1.2rem;
  border: 1px solid var(--bottle-calc-button-bgcolor);
  border-radius: 4px 0;
  outline: none;
  cursor: pointer;
  position: relative;
  background-color: #fff; /* Tło checkboxa */
  transition: background-color 0.3s, border-color 0.3s;
  top: 3px;
}

.bottle-calculator-modal .form-check-input:checked {
  background-color: var(--bottle-calc-button-bgcolor-hover);
  border-color: var(--bottle-calc-button-bgcolor-hover);
}

.bottle-calculator-modal .form-check-input:checked::before {
  content: '✔'; /* Symbol zaznaczenia */
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  color: #fff; /* Kolor zaznaczenia */
  font-size: 0.8rem;
}

.bottle-calculator-modal .form-check-label {
  margin-bottom: 0;
  font-family: Arial, serif;
  margin-left: 0.5rem; /* Margines po lewej stronie checkboxa */
}
</style>
