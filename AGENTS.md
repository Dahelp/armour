# Adding or updating catalog products

- Read the supplied document as product data. Preserve its full text, title, description and every technical characteristic. Preserve real numbered and bulleted lists as HTML lists; do not flatten them into paragraphs.
- Distinguish technical characteristics (`product_attribute`) from catalog filters (`attribute_product`). Fill both before publishing a product.
- Resolve filter groups using their meaning and `url_params`, never numeric IDs copied from another database. Use `ProductDocumentFacets` for tire imports. Include size, brand, tread, rim diameter and pneumatic/solid type where supplied or determined by the document.
- Create a missing filter value and its canonical `url_alias` together. Enable the required groups for the product's category. Do not silently skip missing or ambiguous values.
- Invalidate catalog filter caches after changing bindings or dictionary values, and confirm the new options appear in the live category selector immediately.
- Invalidate catalog filter caches after changing bindings or dictionary values, and confirm the new options appear in the live category selector immediately.
- Build filter links and sitemap entries from registered canonical aliases, never by concatenating a group with its display value. Legacy `.html`, case and slash variants must resolve through `FilterUrlCanonicalizer` to a known canonical page.
- Regenerate sitemap after creating products/filter values. Refresh the tracked `public/sitemap.xml` snapshot so a later full deployment cannot restore stale technical filter URLs.
- Verify the live card's full text, list markup, all technical attributes, short characteristics, category filters, price, stock, photo and canonical URLs. Verify both direct filter pages and category-filter results contain the product.
- Ask for missing price and stock before publishing; do not advertise an unconfirmed zero price as a quoted price.
- Run focused regression checks for list preservation, semantic filter group mapping and `301 -> 200` legacy URLs. Keep normal existing pages working and genuine unknown pages as 404.
