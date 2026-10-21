import { defineConfig } from 'vite';

export default defineConfig({
  esbuild: { jsx: 'automatic', jsxImportSource: 'preact' },
  build: {
    outDir: 'dist',
    emptyOutDir: true,
    lib: {
      entry: 'assets/main.tsx',
      formats: ['iife'],
      name: 'SectionistEditor',
      fileName: () => 'editor.js',
      cssFileName: 'editor',
    },
  },
});
