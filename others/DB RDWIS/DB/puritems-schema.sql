CREATE SCHEMA IF NOT EXISTS puritems;

CREATE TABLE IF NOT EXISTS puritems.categories (
  cat_id SERIAL PRIMARY KEY,
  cat_name VARCHAR(120) UNIQUE NOT NULL
);

CREATE TABLE IF NOT EXISTS puritems.subcategories (
  sub_id SERIAL PRIMARY KEY,
  sub_name VARCHAR(120) NOT NULL,
  sub_cat_id INTEGER NOT NULL REFERENCES puritems.categories(cat_id) ON UPDATE CASCADE ON DELETE CASCADE,
  UNIQUE(sub_cat_id, sub_name)
);

CREATE TABLE IF NOT EXISTS puritems.items (
  itm_id SERIAL PRIMARY KEY,
  itm_title TEXT NOT NULL,
  itm_desc TEXT,
  itm_qtyunit VARCHAR(32) NOT NULL DEFAULT 'unit',
  itm_cat_id INTEGER REFERENCES puritems.categories(cat_id) ON UPDATE CASCADE ON DELETE SET NULL,
  itm_sub_id INTEGER REFERENCES puritems.subcategories(sub_id) ON UPDATE CASCADE ON DELETE SET NULL,
  itm_unt_id INTEGER,
  itm_hed_id INTEGER,
  itm_acc_id INTEGER,
  created_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE UNIQUE INDEX IF NOT EXISTS puritems_items_uq_idx ON puritems.items(
  itm_title,
  itm_qtyunit,
  COALESCE(itm_unt_id,0),
  COALESCE(itm_hed_id,0),
  COALESCE(itm_acc_id,0)
);

DO $$
BEGIN
    IF EXISTS (SELECT 1 FROM pg_constraint WHERE conname='puritems_items_uq' AND connamespace='puritems'::regnamespace) THEN
        ALTER TABLE puritems.items DROP CONSTRAINT puritems_items_uq;
    END IF;
    IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname='puritems_items_uq' AND connamespace='puritems'::regnamespace) THEN
        ALTER TABLE puritems.items
        ADD CONSTRAINT puritems_items_uq UNIQUE (itm_title, itm_qtyunit, itm_unt_id, itm_hed_id, itm_acc_id);
    END IF;
END$$;

CREATE TABLE IF NOT EXISTS puritems.prices (
  prc_id SERIAL PRIMARY KEY,
  prc_itm_id INTEGER NOT NULL REFERENCES puritems.items(itm_id) ON UPDATE CASCADE ON DELETE CASCADE,
  prc_base NUMERIC NOT NULL DEFAULT 0,
  prc_gst NUMERIC NOT NULL DEFAULT 0,
  prc_sst NUMERIC NOT NULL DEFAULT 0,
  prc_gross NUMERIC NOT NULL DEFAULT 0,
  prc_qty NUMERIC NOT NULL DEFAULT 1,
  prc_qtyunit VARCHAR(32) NOT NULL DEFAULT 'unit',
  effective_date DATE NOT NULL DEFAULT CURRENT_DATE
);

CREATE INDEX IF NOT EXISTS puritems_prices_item_idx ON puritems.prices(prc_itm_id);

CREATE TABLE IF NOT EXISTS puritems.rfqs (
  rfq_id SERIAL PRIMARY KEY,
  rfq_title VARCHAR(180) NOT NULL,
  rfq_unt_id INTEGER,
  rfq_created_by INTEGER,
  rfq_status VARCHAR(24) NOT NULL DEFAULT 'draft',
  rfq_total NUMERIC NOT NULL DEFAULT 0,
  created_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS puritems.rfq_items (
  rfi_id SERIAL PRIMARY KEY,
  rfi_rfq_id INTEGER NOT NULL REFERENCES puritems.rfqs(rfq_id) ON UPDATE CASCADE ON DELETE CASCADE,
  rfi_itm_id INTEGER NOT NULL REFERENCES puritems.items(itm_id) ON UPDATE CASCADE ON DELETE CASCADE,
  rfi_price_id INTEGER REFERENCES puritems.prices(prc_id) ON UPDATE CASCADE ON DELETE SET NULL,
  rfi_qty NUMERIC NOT NULL DEFAULT 1,
  rfi_total NUMERIC NOT NULL DEFAULT 0
);

INSERT INTO puritems.categories(cat_name)
SELECT 'Uncategorized'
WHERE NOT EXISTS (SELECT 1 FROM puritems.categories WHERE cat_name='Uncategorized');

INSERT INTO puritems.subcategories(sub_name, sub_cat_id)
SELECT 'Uncategorized', cat_id FROM puritems.categories WHERE cat_name='Uncategorized'
ON CONFLICT DO NOTHING;

