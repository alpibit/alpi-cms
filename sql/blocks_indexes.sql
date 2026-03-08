ALTER TABLE blocks
    ADD INDEX idx_blocks_content_order (content_id, order_num);