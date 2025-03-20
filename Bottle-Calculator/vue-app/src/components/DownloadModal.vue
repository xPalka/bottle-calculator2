<template>
  <div v-if="visible" class="modal-overlay bottle-calculator-modal" @click.self="closeModal">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">5. Pobierz wizualizacje</h5>
      </div>
      <div class="modal-body">

        <form v-if="!successMessage">
          <div class="bottle-download-name-container">
            <input type="email" id="email" v-model="email" class="bottle-download-name" placeholder="e-mail" required />
          </div>
          <div class="bottle-download-name-container" style="margin-top: 16px;">
            <input type="tel" id="telephone" v-model="telephone" class="bottle-download-name" placeholder="numer telefonu" required />
          </div>
          <div style="margin: 16px 0;" class="">
            <input type="checkbox" id="acceptTerms" v-model="acceptTerms" required />
            <label for="acceptTerms" class="form-check-label-bottle-modal"><small>Akceptuję zgodę marketingową, która zawiera </small></label>
            <a @click="toggleTerms" style="color: var(--bottle-calc-button-bgcolor);" class="terms-body-text expand-btn"><small>{{ termsExpanded ? '[ukryj]' : '[...]' }}</small></a>
            <div v-show="termsExpanded" class="terms-body terms-body-text">
              <p><small>ipsum dolor sit amet, consectetur adipiscing elit. Quisque id diam sit amet odio tempus auctor. Aliquam erat volutpat.
                Duis nec nunc sed neque bibendum cursus. Integer ac eros vel orci tempus vestibulum et ac lorem. Aenean auctor justo non
                lorem vehicula, ac tincidunt libero faucibus. Donec pharetra turpis a libero facilisis, ut malesuada ipsum ultricies.</small></p>
            </div>
          </div>
          <div style="margin: 16px 0;" class="">
            <input type="checkbox" id="acceptTerms2" v-model="acceptTerms2" required />
            <label for="acceptTerms2" class="form-check-label-bottle-modal"><small>Akceptuję regulamin, który zawiera </small></label>
            <a @click="toggleTerms2" style="color: var(--bottle-calc-button-bgcolor);" class="terms-body-text expand-btn"><small>{{ termsExpanded ? '[ukryj]' : '[...]' }}</small></a>
            <div v-show="termsExpanded2" class="terms-body terms-body-text">
              <p><small>Nam eget risus at magna dictum bibendum. Vivamus ut sapien ac quam condimentum suscipit. Donec consectetur ex a
                risus volutpat, a pharetra nulla cursus. Cras at lacus ac felis scelerisque facilisis. Integer et volutpat tortor.
                Proin malesuada velit sed mauris lacinia, eget convallis odio sodales. Nullam viverra, arcu in pharetra dapibus,
                metus sem tincidunt orci, sed vehicula erat nisi sed ligula.</small></p>
            </div>
          </div>
          <div style="display: flex; justify-content: center; align-content: center; gap: 2px;">
            <button style="width:50%;" type="button" @click="handleFormSubmit">Pobierz obraz</button>
            <button style="width:50%; background: grey;" type="button" @click="closeModal">Anuluj</button>
          </div>
          <p v-if="errorMessage" class="error-msg">{{ errorMessage }}</p>
          <p v-else style="height: 2px;"></p>
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
      telephone: '',
      acceptTerms: false,
      acceptTerms2: false,
      termsExpanded: false,
      termsExpanded2: false,
      successMessage: '',
      errorMessage: '',
    };
  },
  methods: {
    handleFormSubmit() {
      this.errorMessage = ""; // Resetuje komunikat o błędzie

      if (!this.email || !this.telephone || !this.acceptTerms || !this.acceptTerms2) {
        this.errorMessage = "Wszystkie pola są wymagane!";
      }

      const phonePattern = /^\+?\d{9,15}$/;
      if (!phonePattern.test(this.telephone)) {
        this.errorMessage = "Podaj poprawny numer telefonu (9-15 cyfr)!";
      }

      const emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{1,}$/;
      if (!emailPattern.test(this.email)) {
        this.errorMessage = "Podaj poprawny adres e-mail!";
      }

      if (this.errorMessage === '') {
        this.successMessage = 'Dziękujemy za stworzenie własnej wizualizacji! Zdjęcie już się pobiera!';
        this.$emit('submit', this.email, this.telephone);
      }
    },
    closeModal() {
      this.email = '';
      this.acceptTerms = false;
      this.successMessage = '';
      this.$emit('close');
    },
    toggleTerms() {
      this.termsExpanded = !this.termsExpanded;
    },
    toggleTerms2() {
      this.termsExpanded2 = !this.termsExpanded2;
    }
  },
};
</script>
