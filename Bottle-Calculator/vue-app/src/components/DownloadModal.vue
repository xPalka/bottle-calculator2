<template>
  <div v-if="visible" class="modal-overlay bottle-calculator-modal" @click.self="closeModal">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">{{ translations.modalTitle }}</h5>
      </div>
      <div class="modal-body">

        <form v-if="!successMessage">
          <label class="bottle-download-name-container">
            <b>{{ translations.email }} <span class="text-app-red">*</span></b>
            <input type="email" id="email" v-model="email" class="bottle-download-name"
                   :placeholder="translations.emailPlaceholder" required />
          </label>
          <label class="bottle-download-name-container" style="margin-top: 16px;">
            <b>{{ translations.phone }} <span class="text-app-red">*</span></b>
            <input type="tel" id="telephone" v-model="telephone" class="bottle-download-name"
                   :placeholder="translations.phonePlaceholder" required />
          </label>
          <div style="margin: 16px 0;" class="">
            <input type="checkbox" id="acceptTerms" v-model="acceptTerms" required />
            <label for="acceptTerms" class="form-check-label-bottle-modal">
              <small>{{ translations.acceptMarketing }} </small>
            </label>
            <a @click="toggleTerms" style="color: var(--bottle-calc-button-bgcolor);" class="terms-body-text expand-btn">
              <small> {{ termsExpanded ? translations.hideText : translations.showMore }}</small>
            </a>
            <div v-show="termsExpanded" class="terms-body terms-body-text">
              <p><small>{{ translations.termsText }}</small></p>
            </div>
          </div>
          <div style="margin: 16px 0;" class="">
            <input type="checkbox" id="acceptTerms2" v-model="acceptTerms2" required />
            <label for="acceptTerms2" class="form-check-label-bottle-modal">
              <small>{{ translations.acceptTerms }}</small>
            </label>
            <a @click="toggleTerms2" style="color: var(--bottle-calc-button-bgcolor);" class="terms-body-text expand-btn">
              <small> {{ termsExpanded2 ? translations.hideText : translations.showMore }}</small>
            </a>
            <div v-show="termsExpanded2" class="terms-body terms-body-text">
              <p><small>{{ translations.termsText2 }}</small></p>
            </div>
          </div>
          <div style="display: flex; justify-content: center; align-content: center; gap: 2px;">
            <button style="width:50%;" type="button" @click="handleFormSubmit">
              {{ translations.downloadImage }}
            </button>
            <button style="width:50%; background: grey;" type="button" @click="closeModal">
              {{ translations.cancel }}
            </button>
          </div>
          <p v-if="errorMessage" class="error-msg">{{ errorMessage }}</p>
          <p v-else style="height: 2px;"></p>
        </form>
        <div v-else>
          <p>{{ successMessage }}</p>
          <div style="display:flex; align-content: end; justify-content: end">
            <button style="width:50%;" type="button" @click="closeModal">
              {{ translations.close }}
            </button>
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
    translations: Object,
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
