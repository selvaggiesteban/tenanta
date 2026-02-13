import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'
import { aliases, mdi } from 'vuetify/iconsets/mdi'

const lightTheme = {
  dark: false,
  colors: {
    'primary': '#696CFF',
    'secondary': '#8592A3',
    'success': '#71DD37',
    'info': '#03C3EC',
    'warning': '#FFAB00',
    'error': '#FF3E1D',
    'background': '#F5F5F9',
    'surface': '#FFFFFF',
    'on-primary': '#FFFFFF',
    'on-secondary': '#FFFFFF',
    'on-success': '#FFFFFF',
    'on-info': '#FFFFFF',
    'on-warning': '#FFFFFF',
    'on-error': '#FFFFFF',
    'on-background': '#32475C',
    'on-surface': '#32475C',
  },
}

const darkTheme = {
  dark: true,
  colors: {
    'primary': '#696CFF',
    'secondary': '#8592A3',
    'success': '#71DD37',
    'info': '#03C3EC',
    'warning': '#FFAB00',
    'error': '#FF3E1D',
    'background': '#232333',
    'surface': '#2B2C40',
    'on-primary': '#FFFFFF',
    'on-secondary': '#FFFFFF',
    'on-success': '#FFFFFF',
    'on-info': '#FFFFFF',
    'on-warning': '#FFFFFF',
    'on-error': '#FFFFFF',
    'on-background': '#E4E6F0',
    'on-surface': '#E4E6F0',
  },
}

export default createVuetify({
  components,
  directives,
  icons: {
    defaultSet: 'mdi',
    aliases,
    sets: { mdi },
  },
  theme: {
    defaultTheme: 'light',
    themes: {
      light: lightTheme,
      dark: darkTheme,
    },
  },
  defaults: {
    VBtn: {
      variant: 'flat',
      color: 'primary',
    },
    VTextField: {
      variant: 'outlined',
      density: 'compact',
    },
    VSelect: {
      variant: 'outlined',
      density: 'compact',
    },
    VCard: {
      elevation: 0,
      border: true,
    },
  },
})
