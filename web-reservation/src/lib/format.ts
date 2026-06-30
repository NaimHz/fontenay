/** Libellé lisible d'un service à partir de sa valeur API. */
export function serviceLabel(service: "midi" | "soir"): string {
  return service === "midi" ? "Déjeuner" : "Dîner";
}

/** Formate une date ISO (YYYY-MM-DD) en format français lisible. */
export function formatDate(iso: string): string {
  const [y, m, d] = iso.split("-");
  return `${d}/${m}/${y}`;
}
