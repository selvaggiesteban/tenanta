import { defineCustomElement } from 'vue';
import TenantaWidget from './components/widget/TenantaWidget.vue';

const TenantaWidgetElement = defineCustomElement(TenantaWidget);

customElements.define('tenanta-widget', TenantaWidgetElement);

declare module 'vue' {
  export interface GlobalComponents {
    'tenanta-widget': typeof TenantaWidgetElement;
  }
}
