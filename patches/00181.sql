-- Delete all vocative forms.
-- This is safe while no lexems have the [animate] tag.
delete f from InflectedForm f join Inflection i on f.inflectionId = i.id where i.animate;
