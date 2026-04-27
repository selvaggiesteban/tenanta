<template>
  <div class="tenanta-widget-form">
    <h3>{{ title }}</h3>
    <form @submit.prevent="submitForm">
      <div class="form-group">
        <label for="name">Nombre</label>
        <input type="text" id="name" v-model="form.name" required placeholder="Tu nombre">
      </div>
      <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" v-model="form.email" required placeholder="tu@email.com">
      </div>
      <div class="form-group">
        <label for="message">¿En qué podemos ayudarte?</label>
        <textarea id="message" v-model="form.message" required placeholder="Escribe tu mensaje..."></textarea>
      </div>
      <button type="submit" :disabled="loading">
        {{ loading ? 'Enviando...' : 'Comenzar Chat' }}
      </button>
    </form>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue';

const props = defineProps({
  title: {
    type: String,
    default: 'Chat con nosotros'
  }
});

const emit = defineEmits(['submitted']);

const loading = ref(false);
const form = reactive({
  name: '',
  email: '',
  message: ''
});

const submitForm = () => {
  loading.value = true;
  emit('submitted', { ...form });
};
</script>

<style scoped>
.tenanta-widget-form {
  padding: 20px;
  font-family: sans-serif;
}
.tenanta-widget-form h3 {
  margin-top: 0;
  color: #333;
}
.form-group {
  margin-bottom: 15px;
}
.form-group label {
  display: block;
  margin-bottom: 5px;
  font-size: 14px;
  color: #666;
}
.form-group input, .form-group textarea {
  width: 100%;
  padding: 8px;
  border: 1px solid #ddd;
  border-radius: 4px;
  box-sizing: border-box;
}
.form-group textarea {
  height: 80px;
  resize: none;
}
button {
  width: 100%;
  padding: 10px;
  background-color: #1976D2;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-weight: bold;
}
button:disabled {
  background-color: #ccc;
}
</style>
