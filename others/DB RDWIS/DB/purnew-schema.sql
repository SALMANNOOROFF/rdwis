CREATE SCHEMA IF NOT EXISTS purnew;

DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema='purnew' AND table_name='items') THEN
        EXECUTE 'CREATE VIEW purnew.items AS SELECT * FROM puritems.items';
    END IF;
END$$;

DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema='purnew' AND table_name='prices') THEN
        EXECUTE 'CREATE VIEW purnew.prices AS SELECT * FROM puritems.prices';
    END IF;
END$$;

DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema='purnew' AND table_name='rfqs') THEN
        EXECUTE 'CREATE VIEW purnew.rfqs AS SELECT * FROM puritems.rfqs';
    END IF;
END$$;

DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema='purnew' AND table_name='rfq_items') THEN
        EXECUTE 'CREATE VIEW purnew.rfq_items AS SELECT * FROM puritems.rfq_items';
    END IF;
END$$;
