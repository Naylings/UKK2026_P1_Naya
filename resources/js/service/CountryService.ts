/**
 * UKK2026_P1_Naya\resources\js\service\CountryService.ts
 *
 * Stubs for CountryService and NodeService to satisfy imports during the
 * TypeScript migration. These are minimal, synchronous/async-friendly
 * implementations returning sample data. Replace with real API calls when
 * integrating with a backend.
 */

/* eslint-disable */
// @ts-nocheck - permissive for migration convenience; remove when types are tightened

export type Country = {
  name: string;
  code: string;
  continent?: string;
  population?: number;
};

export const SAMPLE_COUNTRIES: Country[] = [
  { name: 'United States', code: 'US', continent: 'North America', population: 331002651 },
  { name: 'Canada', code: 'CA', continent: 'North America', population: 37742154 },
  { name: 'United Kingdom', code: 'GB', continent: 'Europe', population: 67886011 },
  { name: 'France', code: 'FR', continent: 'Europe', population: 65273511 },
  { name: 'Germany', code: 'DE', continent: 'Europe', population: 83783942 },
  { name: 'India', code: 'IN', continent: 'Asia', population: 1380004385 },
  { name: 'Japan', code: 'JP', continent: 'Asia', population: 126476461 },
  { name: 'Australia', code: 'AU', continent: 'Oceania', population: 25499884 }
];

/**
 * CountryService
 *
 * Provides methods commonly used in UI samples:
 * - getCountries(): Promise<Country[]>
 * - findCountry(code): Promise<Country | undefined>
 */
export const CountryService = {
  /**
   * Return a list of countries (sample data).
   */
  async getCountries(): Promise<Country[]> {
    // Simulate async behaviour for components that expect a Promise
    return Promise.resolve([...SAMPLE_COUNTRIES]);
  },

  /**
   * Find a country by its ISO code.
   */
  async findCountry(code: string): Promise<Country | undefined> {
    const c = SAMPLE_COUNTRIES.find((s) => s.code.toUpperCase() === (code || '').toUpperCase());
    return Promise.resolve(c);
  },

  /**
   * Simple search helper used by AutoComplete examples.
   */
  async search(query: string): Promise<Country[]> {
    if (!query) return Promise.resolve([...SAMPLE_COUNTRIES]);
    const q = query.toLowerCase();
    const results = SAMPLE_COUNTRIES.filter((c) => c.name.toLowerCase().includes(q) || c.code.toLowerCase().includes(q));
    return Promise.resolve(results);
  }
};

export default CountryService;

/* -------------------------------------------------------------------------- */
/* NodeService (tree node stubs)                                               */
/* -------------------------------------------------------------------------- */

export type TreeNode = {
  key: string;
  label?: string;
  data?: any;
  icon?: string;
  children?: TreeNode[];
  leaf?: boolean;
};

/**
 * SAMPLE_TREE - basic hierarchical nodes often used in Tree, TreeSelect, TreeTable
 */
export const SAMPLE_TREE: TreeNode[] = [
  {
    key: '0',
    label: 'Documents',
    icon: 'pi pi-fw pi-folder',
    children: [
      {
        key: '0-0',
        label: 'Work',
        icon: 'pi pi-fw pi-folder',
        children: [
          { key: '0-0-0', label: 'Expenses.doc', icon: 'pi pi-fw pi-file', leaf: true },
          { key: '0-0-1', label: 'Resume.doc', icon: 'pi pi-fw pi-file', leaf: true }
        ]
      },
      {
        key: '0-1',
        label: 'Home',
        icon: 'pi pi-fw pi-folder',
        children: [{ key: '0-1-0', label: 'Invoices.txt', icon: 'pi pi-fw pi-file', leaf: true }]
      }
    ]
  },
  {
    key: '1',
    label: 'Pictures',
    icon: 'pi pi-fw pi-image',
    children: [
      { key: '1-0', label: 'barcelona.jpg', icon: 'pi pi-fw pi-file', leaf: true },
      { key: '1-1', label: 'logo.png', icon: 'pi pi-fw pi-file', leaf: true },
      { key: '1-2', label: 'spain.png', icon: 'pi pi-fw pi-file', leaf: true }
    ]
  },
  {
    key: '2',
    label: 'Movies',
    icon: 'pi pi-fw pi-video',
    children: [
      {
        key: '2-0',
        label: 'Al Pacino',
        icon: 'pi pi-fw pi-folder',
        children: [{ key: '2-0-0', label: 'Scarface.mp4', icon: 'pi pi-fw pi-file', leaf: true }]
      }
    ]
  }
];

/**
 * NodeService
 *
 * Small set of functions returning tree data used in UI components:
 * - getTreeNodes(): Promise<TreeNode[]>
 * - getTreeTableNodes(): Promise<TreeNode[]> (same shape, but can include table-oriented data)
 */
export const NodeService = {
  async getTreeNodes(): Promise<TreeNode[]> {
    return Promise.resolve(JSON.parse(JSON.stringify(SAMPLE_TREE)));
  },

  // For TreeTable examples we might augment nodes with size/type fields in data
  async getTreeTableNodes(): Promise<TreeNode[]> {
    const clone: TreeNode[] = JSON.parse(JSON.stringify(SAMPLE_TREE));
    // Add example data fields expected by Table demos
    const addMeta = (nodes: TreeNode[]) => {
      nodes.forEach((n, i) => {
        n.data = {
          size: `${(Math.floor(Math.random() * 100) + 1) * 10} KB`,
          type: n.leaf ? 'File' : 'Folder'
        };
        if (n.children && n.children.length) addMeta(n.children);
      });
    };
    addMeta(clone);
    return Promise.resolve(clone);
  }
};

export { NodeService };
