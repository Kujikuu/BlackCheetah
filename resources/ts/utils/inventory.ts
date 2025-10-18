export interface InventoryProductResponse {
  id: number
  name: string
  description?: string
  unit_price?: number
  category?: string
  quantity?: number
  reorder_level?: number
}

export interface InventoryProduct {
  id: number
  name: string
  description: string
  unitPrice: number
  category: string
  status: string
  stock: number
  sku: string
  minimumStock: number
}

/**
 * Maps backend inventory response to front-end product shape used in Unit details page.
 */
export function mapInventoryResponse(products: InventoryProductResponse[]): InventoryProduct[] {
  return products.map(product => ({
    id: product.id,
    name: product.name,
    description: product.description || '',
    unitPrice: product.unit_price ?? 0,
    category: product.category || 'General',
    status: 'active',
    stock: product.quantity ?? 0,
    sku: '',
    minimumStock: product.reorder_level ?? 0,
  }))
}
