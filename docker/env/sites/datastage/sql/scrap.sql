


SET @p_id_dtype := 1;
SET @p_url      := 'http://www.google.com.ar/';
SET @p_request  := '{"a": "1"}';

PREPARE S FROM 'CALL dump_job_new (?,?,?,?,?,?);';
EXECUTE S USING  @p_id_dtype,
  @p_url, 
  @p_request,
  @o_id_djob,
  @o_hash,   
  @o_result;

SELECT @o_id_djob, CAST(@o_hash AS CHAR) HASH, @o_result;

SELECT * FROM dump_data;



/ 


BEGIN
DECLARE v_dtype_exists INT;

-- Vars
SET v_dtype_exists := 0;
SET o_result := 1;
SET o_hash := ( SELECT CAST(UUID() AS CHAR) );

-- Validate
SELECT COUNT(1) 
  INTO v_dtype_exists 
  FROM dump_job 
  WHERE id_dtype = p_id_dtype > 0;

IF (o_result = 1 
     AND p_id_dtype IS NOT NULL 
     AND v_dtype_exists > 0) THEN
  SET o_result := 1;    
ELSE
  SET o_result := 0;
END IF;

IF (o_result = 1 
     AND p_url IS NOT NULL) THEN
  SET o_result := 1;   
ELSE
  SET o_result := 0;
END IF;

-- Ejecutar
IF (o_result = 0) THEN
  SET o_result := 0;
ELSE

  INSERT INTO dump_job ( id_dtype, hash, url, request ) 
    VALUES ( p_id_dtype, o_hash, p_url, p_request );

  SELECT id_djob INTO o_id_djob 
    FROM dump_job WHERE hash like o_hash;
  
  COMMIT;
  
END IF;

END