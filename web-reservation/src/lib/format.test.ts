import { describe, expect, it } from "vitest";
import { formatDate, serviceLabel } from "./format";

describe("serviceLabel", () => {
  it("traduit les services", () => {
    expect(serviceLabel("midi")).toBe("Déjeuner");
    expect(serviceLabel("soir")).toBe("Dîner");
  });
});

describe("formatDate", () => {
  it("formate une date ISO en JJ/MM/AAAA", () => {
    expect(formatDate("2026-06-30")).toBe("30/06/2026");
  });
});
