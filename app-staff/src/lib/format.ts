/** Formate un prix en euros au format français : 12.5 -> "12,50 €". */
export function formatPrice(value: number | string): string {
  const n = typeof value === "string" ? parseFloat(value) : value;
  return `${n.toFixed(2).replace(".", ",")} €`;
}
