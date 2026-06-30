import { describe, expect, it } from "vitest";
import { formatPrice } from "./format";

describe("formatPrice", () => {
  it("formate un nombre au format euros français", () => {
    expect(formatPrice(12.5)).toBe("12,50 €");
  });

  it("accepte une chaîne décimale", () => {
    expect(formatPrice("8")).toBe("8,00 €");
  });
});
