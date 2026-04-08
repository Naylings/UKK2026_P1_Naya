import { computed, reactive } from "vue";

type MenuMode = "static" | "overlay" | "slim" | string;

interface LayoutConfig {
  preset: string;
  primary: string;
  surface: string;
  darkTheme: boolean;
  menuMode: MenuMode;
}

interface LayoutState {
  staticMenuInactive: boolean;
  overlayMenuActive: boolean;
  profileSidebarVisible: boolean;
  configSidebarVisible: boolean;
  sidebarExpanded: boolean;
  menuHoverActive: boolean;
  activeMenuItem: string | null;
  activePath: string | null;
  mobileMenuActive: boolean;
  anchored: boolean;
}

/**
 * Composable that exposes layout state and helpers.
 *
 * This is a TypeScript port of the original JS composable.
 */

const layoutConfig = reactive<LayoutConfig>({
  preset: "Lara",
  primary: "orange",
  surface: "stone",
  darkTheme: false,
  menuMode: "static",
});

const layoutState = reactive<LayoutState>({
  staticMenuInactive: false,
  overlayMenuActive: false,
  profileSidebarVisible: false,
  configSidebarVisible: false,
  sidebarExpanded: false,
  menuHoverActive: false,
  activeMenuItem: null,
  activePath: null,
  mobileMenuActive: false,
  anchored: false,
});

export function useLayout() {
  const executeDarkModeToggle = () => {
    layoutConfig.darkTheme = !layoutConfig.darkTheme;
    document.documentElement.classList.toggle("app-dark");
  };

  const toggleDarkMode = () => {
    // document.startViewTransition is an experimental API — guard for availability
    if (!("startViewTransition" in document)) {
      executeDarkModeToggle();
      return;
    }

    // startViewTransition takes a callback — do not pass an undefined event
    // eslint-disable-next-line @typescript-eslint/ban-ts-comment
    // @ts-ignore - startViewTransition is experimental and may not be typed in DOM lib
    document.startViewTransition(() => executeDarkModeToggle());
  };

  const isDesktop = (): boolean => window.innerWidth > 991;

  const toggleMenu = () => {
    if (isDesktop()) {
      if (layoutConfig.menuMode === "static") {
        layoutState.staticMenuInactive = !layoutState.staticMenuInactive;
      }

      if (layoutConfig.menuMode === "overlay") {
        layoutState.overlayMenuActive = !layoutState.overlayMenuActive;
      }
    } else {
      layoutState.mobileMenuActive = !layoutState.mobileMenuActive;
    }
  };

  const toggleConfigSidebar = () => {
    layoutState.configSidebarVisible = !layoutState.configSidebarVisible;
  };

  const hideMobileMenu = () => {
    layoutState.mobileMenuActive = false;
  };

  const changeMenuMode = (event: { value: MenuMode }) => {
    layoutConfig.menuMode = event.value;
    layoutState.staticMenuInactive = false;
    layoutState.mobileMenuActive = false;
    layoutState.sidebarExpanded = false;
    layoutState.menuHoverActive = false;
    layoutState.anchored = false;
  };

  const isDarkTheme = computed(() => layoutConfig.darkTheme);
  const hasOpenOverlay = computed(() => layoutState.overlayMenuActive);

  return {
    layoutConfig,
    layoutState,
    isDarkTheme,
    toggleDarkMode,
    toggleConfigSidebar,
    toggleMenu,
    hideMobileMenu,
    changeMenuMode,
    isDesktop,
    hasOpenOverlay,
  };
}

export type UseLayoutReturn = ReturnType<typeof useLayout>;
