/**
 * resources/js/service/ProductService.ts
 *
 * Minimal stub implementation of a ProductService to satisfy imports during
 * the TypeScript migration. The service provides a few async functions that
 * return sample product data. Replace with real API calls as needed.
 */

export type Product = {
  id: string;
  code?: string;
  name?: string;
  description?: string;
  image?: string;
  price?: number;
  category?: string;
  rating?: number;
  inventoryStatus?: 'INSTOCK' | 'LOWSTOCK' | 'OUTOFSTOCK' | string;
  quantity?: number;
};

const SAMPLE_PRODUCTS: Product[] = [
  {
    id: 'P1',
    code: 'A-101',
    name: 'Space T-Shirt',
    description: 'Comfortable cotton tee',
    image: 'product-placeholder.svg',
    price: 79,
    category: 'Clothing',
    rating: 4,
    inventoryStatus: 'INSTOCK',
    quantity: 120
  },
  {
    id: 'P2',
    code: 'A-102',
    name: 'Portal Sticker',
    description: 'Vinyl sticker',
    image: 'product-placeholder.svg',
    price: 5,
    category: 'Accessories',
    rating: 3,
    inventoryStatus: 'LOWSTOCK',
    quantity: 16
  },
  {
    id: 'P3',
    code: 'A-103',
    name: 'Supernova Sticker',
    description: 'Colorful vinyl sticker',
    image: 'product-placeholder.svg',
    price: 8,
    category: 'Accessories',
    rating: 5,
    inventoryStatus: 'INSTOCK',
    quantity: 67
  },
  {
    id: 'P4',
    code: 'A-104',
    name: 'Wonders Notebook',
    description: 'Spiral notebook',
    image: 'product-placeholder.svg',
    price: 14,
    category: 'Office',
    rating: 4,
    inventoryStatus: 'INSTOCK',
    quantity: 50
  },
  {
    id: 'P5',
    code: 'A-105',
    name: 'Mat Black Case',
    description: 'Protective phone case',
    image: 'product-placeholder.svg',
    price: 24,
    category: 'Accessories',
    rating: 4,
    inventoryStatus: 'INSTOCK',
    quantity: 75
  },
  {
    id: 'P6',
    code: 'A-106',
    name: 'Robots T-Shirt',
    description: 'Graphic tee',
    image: 'product-placeholder.svg',
    price: 32,
    category: 'Clothing',
    rating: 3,
    inventoryStatus: 'OUTOFSTOCK',
    quantity: 0
  },
  {
    id: 'P7',
    code: 'A-107',
    name: 'Matte Mug',
    description: 'Ceramic mug',
    image: 'product-placeholder.svg',
    price: 12,
    category: 'Kitchen',
    rating: 4,
    inventoryStatus: 'INSTOCK',
    quantity: 200
  },
  {
    id: 'P8',
    code: 'A-108',
    name: 'Aurora Lamp',
    description: 'Decorative lamp',
    image: 'product-placeholder.svg',
    price: 48,
    category: 'Home',
    rating: 5,
    inventoryStatus: 'LOWSTOCK',
    quantity: 9
  }
];

/**
 * ProductService
 *
 * Exposes async functions that return product data. These are simple stubs
 * that resolve immediately with sample data. Replace with actual network
 * requests when integrating with a backend.
 */
export const ProductService = {
  /**
   * Return the full list of products.
   */
  getProducts(): Promise<Product[]> {
    return Promise.resolve([...SAMPLE_PRODUCTS]);
  },

  /**
   * Return a smaller set (useful for dashboards / widgets).
   */
  getProductsSmall(): Promise<Product[]> {
    return Promise.resolve(SAMPLE_PRODUCTS.slice(0, 6));
  },

  /**
   * Return products but simulate a small async delay (optional).
   * Useful when components expect asynchronous loading.
   */
  async getProductsWithDelay(delay = 150): Promise<Product[]> {
    await new Promise((res) => setTimeout(res, delay));
    return [...SAMPLE_PRODUCTS];
  },

  /**
   * Find a single product by id
   */
  findById(id: string): Promise<Product | undefined> {
    const p = SAMPLE_PRODUCTS.find((x) => x.id === id);
    return Promise.resolve(p);
  }
};
