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

Homepage selection takes priority over promotional lists: one H1 in the hero, a size/article GET form with native datalist suggestions, canonical links for known sizes, then equipment categories including ATV/UTV and a separate parts section. The full directory is an accessible native disclosure. Hero copy must reflect both industrial and quad-bike tires. CSS and progressive enhancement are owned by home-selection.css and home-selection.js and loaded only on Main. Search without JavaScript remains supported; exact-match enhancement never constructs aliases from display sizes. No automatic competitor research is part of implementation.

The hero selector blends into the industrial photograph with a compact dark navy surface, a fine border and a restrained green top edge. Reserve the light surface for the input itself; avoid a large white panel competing with the photograph and main heading.

Equipment and parts entry cards use a newly generated matching image family: unbranded navy machinery, small green accents, soft studio light and a pale cool background. Canonical media lives in public/images/home-selection/*.webp; it illustrates categories and must not be reused as photographs of actual inventory. Desktop equipment cards pair text with an image; narrow cards put the image above the text. Keep imagery uncropped with contain, reserve dimensions, lazy-load it, and retain text links. No decorative arrows in the homepage selection journey. The specialist contact band uses the same dark navy family as the hero.

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

Never replace authored metadata or full HTML/list content. StorefrontSeo supplies render-time defaults when fields are empty. Filter headings derive from semantic group and value, since attribute_value has no name column. Catalog technical attributes and filters remain distinct. Unconfirmed zero prices display as request-only, without a zero-price Offer or purchase action.

## Accessibility

Semantic product lists, descriptive links, labeled quantity controls, keyboard focus and reduced-motion support. Full product text and specifications remain on the detail page.

## Verification

StorefrontSeoTest covers empty metadata, canonical preservation and authored lists. UnifiedProductGridTest checks direct list children, stock and zero-price states. Existing catalog/search/SEO regression checks remain required. Verify desktop and narrow viewport in a browser before delivery.
