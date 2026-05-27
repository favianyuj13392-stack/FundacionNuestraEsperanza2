module.exports = {
  content: [
    "./src/pages/**/*.{js,ts,jsx,tsx,mdx}",
    "./src/components/**/*.{js,ts,jsx,tsx,mdx}",
    "./src/app/**/*.{js,ts,jsx,tsx,mdx}",
  ],
  theme: {
    extend: {
      colors: {
        'azul-marino':'#12296C',
        'rosa-principal': '#FF5087',
        'celeste-fondo': '#59ADD3',
        'verde-lima': '#88B13F',
        'turquesa-secundario':'#00C1CA',
        'amarillo-detalle':'#FFAE00',
        'celeste-claro':'#ADE5FF',
        'rosa-claro':'#FFD5E2',
        'verde-lima-claro':'#F1FFD9',
        'beige-claro':'#F6F4E9',
        'amarillo-claro':'#FFE8B7',
        'white': '#FFFFFF',
        'black': '#000000',
      },
      fontFamily: {
        sans:['var(--font-mplus)'], //subtítulos y texto
        title:['var(--font-surfer)'],//títulos
        button:['var(--font-gluten)'],//botones
      },
      keyframes: {
        'fade-in-down': {
          '0%': { opacity: '0', transform: 'translateY(-10px)' },
          '100%': { opacity: '1', transform: 'translateY(0)' },
        },
        'scale-up': {
          '0%': { opacity: '0', transform: 'scale(0.95)' },
          '100%': { opacity: '1', transform: 'scale(1)' },        },
        'fade-in': {
          '0%': { opacity: '0' },
          '100%': { opacity: '1' }        }
      },
      animation: {
        'fade-in-down': 'fade-in-down 0.5s ease-out',
        'fade-in': 'fade-in 0.3s ease-in',
        'scale-up': 'scale-up 0.3s ease-out forwards',
      },
      zIndex: {
        '1000': '1000',
        '9999': '9999',
      }
    },
  },
  plugins: [],
}