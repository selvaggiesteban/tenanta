<script setup lang="ts">
import { onMounted } from 'vue';

// Props o lógica de datos para la landing dinámica
defineProps<{
  slug?: string;
  branding?: any;
}>();

onMounted(() => {
  console.log('Landing Page Soberana Cargada');
});
</script>

<template>
  <div class="landing-soberana">
    <header class="title-section">
      <h1>Ecosistema Funcional Tenanta V1.0</h1>
      <p>Arquitectura Genérica y Soberana de Software</p>
    </header>

    <main class="grid-container">
      <!-- Onboarding -->
      <div class="feature-card card-onboarding">
        <span class="tag tag-onboarding">Onboarding</span>
        <h2>Gestión de Negocio</h2>
        <ul>
          <li><strong>Módulo:</strong> Carga Masiva Automatizada</li>
          <li><strong>Proceso:</strong> Mapeo Dinámico de Perfiles</li>
          <li><strong>Resultado:</strong> Landing SEO Pro</li>
        </ul>
      </div>

      <!-- Finanzas -->
      <div class="feature-card card-finanzas">
        <span class="tag tag-finanzas">Finanzas</span>
        <h2>Inteligencia Financiera</h2>
        <ul>
          <li><strong>Módulo:</strong> Transcriptor de Resúmenes</li>
          <li><strong>Proceso:</strong> Análisis de Datos IA</li>
          <li><strong>Resultado:</strong> Dashboard de Rentabilidad</li>
        </ul>
      </div>

      <!-- Comunicación -->
      <div class="feature-card card-comunicacion">
        <span class="tag tag-comunicacion">Comunicación</span>
        <h2>Hub Omnicanal</h2>
        <ul>
          <li><strong>Módulo:</strong> Bandeja de Entrada Unificada</li>
          <li><strong>Proceso:</strong> Automatización de Mensajería</li>
          <li><strong>IA:</strong> Asistente de Ventas Integrado</li>
        </ul>
      </div>

      <!-- Operaciones -->
      <div class="feature-card card-operaciones">
        <span class="tag tag-operaciones">Operaciones</span>
        <h2>Monitor de Productividad</h2>
        <ul>
          <li><strong>Módulo:</strong> Control de Tiempo y Tareas</li>
          <li><strong>Proceso:</strong> Clasificación de Eficiencia</li>
          <li><strong>Visual:</strong> Planificador de Horarios</li>
        </ul>
      </div>

      <!-- Educación -->
      <div class="feature-card card-educacion">
        <span class="tag tag-educacion">Educación</span>
        <h2>Centro de Formación (LMS)</h2>
        <ul>
          <li><strong>Módulo:</strong> Aula Virtual Profesional</li>
          <li><strong>Poder:</strong> Gestión de Cronogramas</li>
          <li><strong>Pasarela:</strong> Sistema de Cobros Integrado</li>
        </ul>
      </div>

      <!-- Distribución -->
      <div class="feature-card card-distribucion">
        <span class="tag tag-distribucion">Distribución</span>
        <h2>Panel de Distribuidor</h2>
        <ul>
          <li><strong>Módulo:</strong> Gestión de Red de Clientes</li>
          <li><strong>Marca:</strong> Personalización Marca Blanca</li>
          <li><strong>Finanzas:</strong> Control de Beneficios</li>
        </ul>
      </div>
    </main>

    <footer class="action-footer no-print">
      <button @click="window.print()" class="btn-print">
        🖨️ IMPRIMIR MAPA DE SISTEMAS
      </button>
    </footer>
  </div>
</template>

<style scoped>
.landing-soberana {
  --sys-a: #673DE6;
  --sys-b: #10B981;
  --sys-c: #00A9A5;
  --sys-d: #F59E0B;
  --sys-e: #EF4444;
  --sys-f: #3B82F6;
  --bg: #F8FAFC;
  --text: #1E293B;
  
  font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
  background-color: var(--bg);
  color: var(--text);
  min-height: 100vh;
  padding: 40px 20px;
}

.title-section {
  text-align: center;
  margin-bottom: 50px;
}

.title-section h1 {
  font-size: 2.5rem;
  color: var(--sys-a);
  text-transform: uppercase;
  margin-bottom: 10px;
  font-weight: 800;
}

.title-section p {
  font-size: 1.2rem;
  color: #64748b;
}

.grid-container {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 30px;
  max-width: 1200px;
  margin: 0 auto;
}

.feature-card {
  background: white;
  border-radius: 24px;
  padding: 30px;
  border-top: 10px solid;
  box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.feature-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

.card-onboarding { border-top-color: var(--sys-a); }
.card-finanzas { border-top-color: var(--sys-b); }
.card-comunicacion { border-top-color: var(--sys-c); }
.card-operaciones { border-top-color: var(--sys-d); }
.card-educacion { border-top-color: var(--sys-e); }
.card-distribucion { border-top-color: var(--sys-f); }

.tag {
  font-size: 0.7rem;
  font-weight: 800;
  text-transform: uppercase;
  padding: 6px 12px;
  color: white;
  border-radius: 8px;
  display: inline-block;
  margin-bottom: 20px;
  letter-spacing: 0.05em;
}

.tag-onboarding { background-color: var(--sys-a); }
.tag-finanzas { background-color: var(--sys-b); }
.tag-comunicacion { background-color: var(--sys-c); }
.tag-operaciones { background-color: var(--sys-d); }
.tag-educacion { background-color: var(--sys-e); }
.tag-distribucion { background-color: var(--sys-f); }

h2 {
  font-size: 1.4rem;
  margin: 0 0 20px 0;
  color: #0f172a;
  font-weight: 700;
}

ul {
  padding: 0;
  list-style: none;
  font-size: 0.95rem;
  line-height: 2;
  color: #475569;
}

ul li strong {
  color: #1e293b;
  font-weight: 600;
}

.action-footer {
  text-align: center;
  margin-top: 60px;
}

.btn-print {
  padding: 16px 32px;
  background-color: var(--sys-a);
  color: white;
  border: none;
  border-radius: 12px;
  cursor: pointer;
  font-weight: 700;
  font-size: 1rem;
  transition: background-color 0.2s;
  box-shadow: 0 10px 15px -3px rgba(103, 61, 230, 0.3);
}

.btn-print:hover {
  background-color: #5833c7;
}

@media print {
  .no-print { display: none; }
  .landing-soberana { padding: 0; background: white; }
  .feature-card { box-shadow: none; border: 1px solid #e2e8f0; }
}
</style>
