/**
 * shims-vue.d.ts
 *
 * Project-wide TypeScript shims to reduce SFC / third-party import errors when
 * migrating .vue files to <script setup lang="ts">.
 *
 * Place at: UKK2026_P1_Naya\resources\js\shims-vue.d.ts
 */

/* eslint-disable */
/* tslint:disable */

declare module "*.vue" {
  import type { DefineComponent } from "vue";

  /**
   * LooseObject is a permissive indexable type used during the JS -> TS migration
   * to allow accessing arbitrary properties on plain objects (common in templates
   * and existing untyped services).
   */
  type LooseObject = {
    [key: string]: any;
  };

  const component: DefineComponent<LooseObject, LooseObject, any>;
  export default component;
}

/**
 * Allow property access on Object instances (plain JS objects) so expressions like
 * `someObj.foo` do not error during the migration when `someObj` has no strict type.
 *
 * This is intentionally permissive to reduce friction while converting many files
 * to TypeScript. You may tighten these typings later as you annotate individual
 * modules and components.
 */
declare global {
  interface Object {
    [key: string]: any;
  }
}

declare module "*.scss" {
  const _: { [className: string]: string };
  export default _;
}

declare module "*.css" {
  const _: { [className: string]: string };
  export default _;
}

/**
 * PrimeVue: many projects import components from the package entry or from
 * specific paths like 'primevue/button'. Provide permissive module declarations
 * to avoid TS errors while the project opts into more specific typing later.
 */
declare module "primevue" {
  const PV: any;
  export = PV;
}

declare module "primevue/*" {
  const comp: any;
  export default comp;
}

/**
 * Some theme / utility packages used in this project may not have complete
 * TypeScript declarations. Provide a permissive fallback so imports don't fail.
 */
declare module "@primeuix/*" {
  const mod: any;
  export default mod;
}

declare module "@primeuix/themes" {
  const themes: any;
  export = themes;
}

/**
 * Other common runtime imports without types (services, data files, assets).
 * Add here as needed to silence TS errors for non-Typed third-party code.
 */
declare module "@/assets/*" {
  const content: any;
  export default content;
}
declare module "@/*" {
  const value: any;
  export default value;
}

/**
 * Experimental DOM API used by the project (guarded at runtime).
 * Provide minimal typing so TS won't complain when code checks or calls it.
 */
declare global {
  interface Document {
    // startViewTransition is experimental - may not exist on all platforms
    startViewTransition?: (callback: () => void) => void;
  }

  // Relax window if code attaches custom globals
  interface Window {
    [key: string]: any;
  }
}

export {};
