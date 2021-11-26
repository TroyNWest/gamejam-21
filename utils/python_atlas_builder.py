import json
def generateAtlas(height, width, sprite_frames_x, sprite_frames_y):
    matt_ref = [{"name":"wall_tl","x":0,"y":0},{"name":"wall_t","x":8,"y":0},{"name":"wall_door_top","x":16,"y":0},{"name":"wall_tr","x":24,"y":0},{"name":"man_cloth","x":32,"y":0},{"name":"man_leather","x":40,"y":0},{"name":"man_chain","x":48,"y":0},{"name":"man_vest","x":56,"y":0},{"name":"female_dress","x":64,"y":0},{"name":"robed","x":72,"y":0},{"name":"skeleton","x":80,"y":0},{"name":"orc","x":88,"y":0},{"name":"crab","x":96,"y":0},{"name":"beholder","x":104,"y":0},{"name":"female_skirt","x":112,"y":0},{"name":"warlock","x":120,"y":0},{"name":"wall_l","x":0,"y":8},{"name":"floor","x":8,"y":8},{"name":"floor_tile","x":16,"y":8},{"name":"wall_r","x":24,"y":8},{"name":"snake","x":32,"y":8},{"name":"dog","x":40,"y":8},{"name":"rat","x":48,"y":8},{"name":"bug","x":56,"y":8},{"name":"ghoul","x":64,"y":8},{"name":"ghost","x":72,"y":8},{"name":"turtle","x":80,"y":8},{"name":"imp","x":88,"y":8},{"name":"treant","x":96,"y":8},{"name":"podium","x":104,"y":8},{"name":"podium_broken","x":112,"y":8},{"name":"flag_square","x":120,"y":8},{"name":"wall_bl","x":0,"y":16},{"name":"wall_window","x":8,"y":16},{"name":"wall_door_bottom","x":16,"y":16},{"name":"wall_br","x":24,"y":16},{"name":"door_closed","x":32,"y":16},{"name":"door_open","x":40,"y":16},{"name":"door_baricaded","x":48,"y":16},{"name":"door_locked","x":56,"y":16},{"name":"cauldron","x":64,"y":16},{"name":"pot","x":72,"y":16},{"name":"minecart","x":80,"y":16},{"name":"track_tl","x":88,"y":16},{"name":"track_horiz","x":96,"y":16},{"name":"track_end","x":104,"y":16},{"name":"bars","x":112,"y":16},{"name":"flag_triangle","x":120,"y":16},{"name":"pillar_l","x":0,"y":24},{"name":"pillar_r","x":8,"y":24},{"name":"wall_r_2","x":16,"y":24},{"name":"wall_l_2","x":24,"y":24},{"name":"stairs_down","x":32,"y":24},{"name":"stairs_up","x":40,"y":24},{"name":"table","x":48,"y":24},{"name":"table_large","x":56,"y":24},{"name":"stool","x":64,"y":24},{"name":"chest","x":72,"y":24},{"name":"club","x":80,"y":24},{"name":"unknown_1","x":88,"y":24},{"name":"unknown_2","x":96,"y":24},{"name":"unknown_3","x":104,"y":24},{"name":"bed","x":112,"y":24},{"name":"dialog_box","x":120,"y":24},{"name":"wall_tl_2","x":0,"y":32},{"name":"wall_tr_2","x":8,"y":32},{"name":"wall_t_2","x":16,"y":32},{"name":"wall_t3","x":24,"y":32},{"name":"grass","x":32,"y":32},{"name":"grass_2","x":40,"y":32},{"name":"sword","x":48,"y":32},{"name":"axe","x":56,"y":32},{"name":"bow","x":64,"y":32},{"name":"arrow","x":72,"y":32},{"name":"trident","x":80,"y":32},{"name":"cabinet_l","x":88,"y":32},{"name":"cabinet_r","x":96,"y":32},{"name":"mirror","x":104,"y":32},{"name":"boat","x":112,"y":32},{"name":"raft","x":120,"y":32},{"name":"wall_t_left","x":0,"y":40},{"name":"wall_t_right","x":8,"y":40},{"name":"wall_bl_2","x":16,"y":40},{"name":"wall_br_2","x":24,"y":40},{"name":"tree_pine","x":32,"y":40},{"name":"tree","x":40,"y":40},{"name":"tree_2","x":48,"y":40},{"name":"tree_palm","x":56,"y":40},{"name":"coin","x":64,"y":40},{"name":"ring","x":72,"y":40},{"name":"key","x":80,"y":40},{"name":"ladder","x":88,"y":40},{"name":"table_fancy","x":96,"y":40},{"name":"sign","x":104,"y":40},{"name":"river_tl","x":112,"y":40},{"name":"river_vert","x":120,"y":40},{"name":"trapdoor_closed","x":0,"y":48},{"name":"trapdoor_open","x":8,"y":48},{"name":"button_up","x":16,"y":48},{"name":"ui_move_small","x":24,"y":48},{"name":"heart_empty","x":32,"y":48},{"name":"heart_half","x":40,"y":48},{"name":"heart_full","x":48,"y":48},{"name":"shield_empty","x":56,"y":48},{"name":"shield_half","x":64,"y":48},{"name":"shield_full","x":72,"y":48},{"name":"shield","x":80,"y":48},{"name":"well","x":88,"y":48},{"name":"river_horiz","x":96,"y":48},{"name":"river_vert_2","x":104,"y":48},{"name":"water_br_2","x":112,"y":48},{"name":"water_bl_2","x":120,"y":48},{"name":"ui_move_start","x":0,"y":56},{"name":"ui_move_horiz","x":8,"y":56},{"name":"ui_move_turn","x":16,"y":56},{"name":"ui_move_large","x":24,"y":56},{"name":"dialog_quest","x":32,"y":56},{"name":"house","x":40,"y":56},{"name":"castle","x":48,"y":56},{"name":"cave","x":56,"y":56},{"name":"grave","x":64,"y":56},{"name":"grave_2","x":72,"y":56},{"name":"volcano","x":80,"y":56},{"name":"water_tl","x":88,"y":56},{"name":"water_top","x":96,"y":56},{"name":"water_tr","x":104,"y":56},{"name":"water_tr_2","x":112,"y":56},{"name":"water_tl_2","x":120,"y":56},{"name":"magic_1","x":0,"y":64},{"name":"magic_2","x":8,"y":64},{"name":"magic_3","x":16,"y":64},{"name":"magic_4","x":24,"y":64},{"name":"magic_5","x":32,"y":64},{"name":"magic_6","x":40,"y":64},{"name":"magic_7","x":48,"y":64},{"name":"potion","x":56,"y":64},{"name":"fire","x":64,"y":64},{"name":"firepit","x":72,"y":64},{"name":"meat","x":80,"y":64},{"name":"water_l","x":88,"y":64},{"name":"water","x":96,"y":64},{"name":"water_r","x":104,"y":64},{"name":"water_river_t","x":112,"y":64},{"name":"water_river_r","x":120,"y":64},{"name":"wall_tl_3","x":0,"y":72},{"name":"wall_t_3","x":8,"y":72},{"name":"wall_door_top_2","x":16,"y":72},{"name":"wall_tr_3","x":24,"y":72},{"name":"wall_vert_3","x":32,"y":72},{"name":"wall_door_vert","x":40,"y":72},{"name":"wall_bl_3","x":48,"y":72},{"name":"wall_window_3","x":56,"y":72},{"name":"wall_door_bottom_2","x":64,"y":72},{"name":"wall_br_3","x":72,"y":72},{"name":"well_empty","x":80,"y":72},{"name":"water_bl","x":88,"y":72},{"name":"water_b","x":96,"y":72},{"name":"water_br","x":104,"y":72},{"name":"water_river_l","x":112,"y":72},{"name":"water_river_b","x":120,"y":72}]
    matt_ref_place = 0
    x_pos = 0
    y_pos = 0
    atlas_frames = []
    for frames_y in range(sprite_frames_y):
        for frames_x in range(sprite_frames_x):
            frame = {
                "filename": matt_ref[matt_ref_place]["name"],
                "frame": {"x":x_pos,"y":y_pos,"w":width,"h":height},
                "rotated": False,
                "trimmed": False,
                "spriteSourceSize": {"x":0,"y":0,"w":width,"h":height},
                "sourceSize": {"w":width,"h":height},
                "pivot": {"x":.5,"y":.5}
            }
            matt_ref_place += 1
            x_pos += width
            atlas_frames.append(frame)
        y_pos += height
    return atlas_frames

atlas_output = {
    "meta": {
    "version": "1.0",
    "image": "colored_tilemap.png",
    "format": "png",
    "size": {"w":128,"h":80},
    "scale": "1"},
    "frames": generateAtlas(8, 8, 16, 10)
}


with open('rogue_atlas.json', 'w') as outfile:
    json.dump(atlas_output, outfile, indent=4)