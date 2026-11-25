---
version: alpha
name: TechTires
description: Industrial tire catalog for Russian-speaking buyers
colors:
  primary: "#004a8e"
  action: "#008f00"
  surface: "#ffffff"
  border: "#dfe6eb"
  text: "#132d43"
typography:
  sans:
    fontFamily: "Verdana, Arial, sans-serif"
rounded:
  DEFAULT: "8px"
spacing:
  card-gap: "20px"
components:
  card: {}
  catalog: {}
---

# TechTires Design System

## Overview

Russian B2B/B2C industrial tire and parts catalog. Keep the established blue/green identity and technical, readable product names. Primary task: compare size, stock and price, then open the product or add it to the cart. Evidence: catalog templates, product data and existing storefront. Product listings use a product register; the homepage retains its industrial photograph.

Homepage selection takes priority over promotional lists: one H1 in the hero, a size/article GET form with native datalist suggestions, canonical links for known sizes, then equipment categories including ATV/UTV and a separate parts section. Do not add a duplicate full-category directory disclosure to the homepage. Hero copy must reflect both industrial and quad-bike tires. CSS and progressive enhancement are owned by home-selection.css and home-selection.js and loaded only on Main. Search without JavaScript remains supported; exact-match enhancement never constructs aliases from display sizes. No automatic competitor research is part of implementation.

The hero selector blends into the industrial photograph with a compact dark navy surface, a fine border and a restrained green top edge. Reserve the light surface for the input itself; avoid a large white panel competing with the photograph and main heading.

Equipment and parts entry cards use a newly generated matching image family: unbranded navy machinery, small green accents, soft studio light and a pale cool background. Canonical media lives in public/images/home-selection/*.webp; it illustrates categories and must not be reused as photographs of actual inventory. Desktop equipment cards pair text with an image; narrow cards put the image above the text. Keep imagery uncropped with contain, reserve dimensions, lazy-load it, and retain text links. No decorative arrows in the homepage selection journey. The specialist contact band uses the same dark navy family as the hero.

Homepage purchase guidance continues this family: pale assurance surface, quiet product section headings, a navy four-step order panel and a two-column contact/FAQ section with native details disclosures and plus/minus indicators. Mobile uses a single column and preserves the ordered steps. Conditions are sourced from the published /dostavka page; do not invent delivery promises, discounts, reviews or guarantees. HomeProductSelections owns stock/price eligibility, five-product limits and deduplication (promotion, hit, new priority). All cards still use the canonical product renderer. Homepage selections use five columns from 1280px; narrower layouts keep the shared responsive grid. Empty selections have no heading or shell. Existing authored SEO copy remains complete and server-rendered, with matching spacing and typography.

The shared footer is owned by partials/store-footer.php and store-footer.css, loaded once by watches.php on every storefront route. Continue the navy homepage purchase panel with a green top rule, a size/equipment selection action, four desktop columns and a single mobile column. Prioritize tire sizes, equipment, parts, purchase information and a clearly labeled pickup address; legal identity and policies remain at the bottom. Use real category links, including separate camera and rim-flap pages, never the broken combined alias. Footer phones and hours are not duplicated in separate bands. Homepage SEO copy links to relevant existing categories without rewriting authored text. No footer-specific JavaScript, animation or map embed.

## Colors

Blue identifies navigation and product links. Green marks purchase actions. White surfaces and subtle borders separate cards. Status always includes text, not color alone. Runtime owner for card colors: public/css/armour/product-card.css; this document mirrors that source.

## Typography

Verdana with Arial/sans-serif fallback, including Cyrillic. Product names use three lines and retain their full accessible/link title. Numeric tire notation uses Latin x; meaningful dashes, fractions and R are preserved.

## Layout

All catalog product lists use ul.products.product-grid with direct li product children. A responsive auto-fill grid uses a 240px minimum track and 20px gap; below 600px it becomes one column. Product imagery reserves 205px height, uses contain and never crops the tire. Cards keep three title lines, compact spacing, stock quantities and a clear price without a redundant label. Tire comparisons use size, model and ply; size-specific pages omit the repeated size. Missing model data never causes an unrelated field to replace it. Authored SEO HTML follows pagination, independent of inventory state.

## Elevation & Depth

Subtle card borders and shadows match the existing storefront. Hover elevation is restrained and disabled for reduced motion.

## Shapes

Cards use 8px corners. Controls retain the established rectangular green action style and visible blue focus outlines.

## Components

Canonical PHP card owner: app/widgets/product/product_tpl.php. Canonical list renderer: app/widgets/product/Product.php::listing. Canonical styles: public/css/armour/product-card.css, loaded once from layouts/watches.php. Shared cart behavior remains in public/js/main.js; no page-specific card event scripts. Category, filter, search, homepage, content-related and product recommendation lists share this owner. Legacy table templates are not used by storefront listings.

## Content

The /tiposize directory uses TireSizeDirectory for visible-product counts, stock counts, natural ordering and registered canonical links. It has a GET search with immediate local filtering, equivalent x/*/Cyrillic notation, an explicit clear action, and a stock checkbox; R and slash notation remain distinct. Pale search panel and compact bordered size tiles continue the homepage palette. Empty registered size pages remain reachable with a message and a /tiposize return link, use noindex/follow, and preserve authored SEO content. Count and paginated product queries share the same visible-product predicate. Bindings repaired from a tire's explicit size characteristic use ProductDocumentFacets::bindSize; do not put disks or cameras into tire results merely because a compatible size is present.

Never replace authored metadata or full HTML/list content. StorefrontSeo supplies render-time defaults when fields are empty. Filter headings derive from semantic group and value, since attribute_value has no name column. Catalog technical attributes and filters remain distinct. Unconfirmed zero prices display as request-only, without a zero-price Offer or purchase action.

## Accessibility

Semantic product lists, descriptive links, labeled quantity controls, keyboard focus and reduced-motion support. Full product text and specifications remain on the detail page.

## Verification

StorefrontSeoTest covers empty metadata, canonical preservation and authored lists. UnifiedProductGridTest checks direct list children, stock and zero-price states. Existing catalog/search/SEO regression checks remain required. Verify desktop and narrow viewport in a browser before delivery.
