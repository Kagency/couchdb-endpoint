
create view v_last_rev as 
select *
  from document_update 
  where (d_id,du_sequence) in (
SELECT d_id,
       max(du_sequence)
  FROM document_update 
 GROUP BY d_id);
