#!/usr/bin/env python3
"""Generate Physical ER Diagram for KOCH system in draw.io format
   - Crow's Foot Notation (ERmandOne / ERmany)
   - Orthogonal edge routing
   - Smart exit/entry based on relative table position
"""

ROW_H = 26
TBL_W = 240
HDR_H = 28

# ============================================================
# TABLE DEFINITIONS
# ============================================================
tables = {
    "companies": [
        ("PK","id","INT AI"),("UK","code","VARCHAR(50)"),("","name","VARCHAR(100)"),
        ("","description","TEXT"),("","logo_url","VARCHAR(255)"),("","website_url","VARCHAR(255)"),
        ("","is_active","TINYINT(1)"),("","created_at","DATETIME"),("","updated_at","DATETIME"),
    ],
    "users": [
        ("PK","id","INT AI"),("FK","company_id","INT"),("UK","username","VARCHAR(50)"),
        ("UK","email","VARCHAR(100)"),("","password_hash","VARCHAR(255)"),
        ("","first_name","VARCHAR(50)"),("","last_name","VARCHAR(50)"),("","nick_name","VARCHAR(50)"),
        ("","phone","VARCHAR(20)"),("","role","ENUM"),("","is_active","TINYINT(1)"),
        ("","last_login","DATETIME"),("","created_at","DATETIME"),("","updated_at","DATETIME"),
    ],
    "products": [
        ("PK","id","INT AI"),("","name","VARCHAR(100)"),("","description","TEXT"),
        ("","category","VARCHAR(50)"),("","image_url","VARCHAR(255)"),("","display_order","INT"),
        ("","is_active","TINYINT(1)"),("","created_at","DATETIME"),("","updated_at","DATETIME"),
    ],
    "koch_quotations": [
        ("PK","id","INT AI"),("FK","user_id","INT"),("FK","company_id","INT"),
        ("FK","product_id","INT"),("FK","quoted_by","INT"),("UK","quotation_number","VARCHAR(50)"),
        ("","first_name","VARCHAR(50)"),("","last_name","VARCHAR(50)"),("","email","VARCHAR(100)"),
        ("","phone","VARCHAR(20)"),("","company_name","VARCHAR(100)"),("","product_type","VARCHAR(50)"),
        ("","quantity","INT"),("","specifications","TEXT"),("","status","ENUM"),
        ("","quoted_at","DATETIME"),("","created_at","DATETIME"),("","updated_at","DATETIME"),
    ],
    "sliders": [
        ("PK","id","INT AI"),("FK","company_id","INT"),("","title","VARCHAR(100)"),
        ("","subtitle","VARCHAR(200)"),("","image_url","VARCHAR(255)"),("","button_text","VARCHAR(50)"),
        ("","button_url","VARCHAR(255)"),("","display_order","INT"),("","is_active","TINYINT(1)"),
        ("","created_at","DATETIME"),("","updated_at","DATETIME"),
    ],
    "partners": [
        ("PK","id","INT AI"),("FK","company_id","INT"),("","name","VARCHAR(100)"),
        ("","description","TEXT"),("","logo_url","VARCHAR(255)"),("","website_url","VARCHAR(255)"),
        ("","display_order","INT"),("","is_active","TINYINT(1)"),("","created_at","DATETIME"),
        ("","updated_at","DATETIME"),
    ],
    "featured_products": [
        ("PK","id","INT AI"),("FK","company_id","INT"),("","name","VARCHAR(100)"),
        ("","description","TEXT"),("","image_url","VARCHAR(255)"),("","display_order","INT"),
        ("","is_active","TINYINT(1)"),("","created_at","DATETIME"),("","updated_at","DATETIME"),
    ],
    "contact_messages": [
        ("PK","id","INT AI"),("FK","company_id","INT"),("","name","VARCHAR(100)"),
        ("","email","VARCHAR(100)"),("","phone","VARCHAR(20)"),("","subject","VARCHAR(200)"),
        ("","message","TEXT"),("","status","ENUM"),("","ip_address","VARCHAR(45)"),
        ("","created_at","DATETIME"),("","updated_at","DATETIME"),
    ],
    "system_settings": [
        ("PK","id","INT AI"),("FK","company_id","INT"),("UK","setting_key","VARCHAR(100)"),
        ("","setting_value","TEXT"),("","data_type","VARCHAR(20)"),("","description","VARCHAR(255)"),
        ("","category","VARCHAR(50)"),("","is_system","TINYINT(1)"),("","created_at","DATETIME"),
        ("","updated_at","DATETIME"),
    ],
    "truck_types": [
        ("PK","id","INT AI"),("FK","company_id","INT"),("","name","VARCHAR(100)"),
        ("","code","VARCHAR(50)"),("","capacity_tons","DECIMAL(10,2)"),("","description","TEXT"),
        ("","image_url","VARCHAR(255)"),("","display_order","INT"),("","is_active","TINYINT(1)"),
        ("","created_at","DATETIME"),("","updated_at","DATETIME"),
    ],
    "notifications": [
        ("PK","id","INT AI"),("FK","user_id","INT"),("FK","company_id","INT"),
        ("","title","VARCHAR(200)"),("","message","TEXT"),("","type","ENUM"),
        ("","related_table","VARCHAR(50)"),("","related_id","INT"),("","is_read","TINYINT(1)"),
        ("","is_email_sent","TINYINT(1)"),("","priority","INT"),("","email_sent_at","DATETIME"),
        ("","created_at","DATETIME"),
    ],
    "activity_logs": [
        ("PK","id","INT AI"),("FK","user_id","INT"),("FK","company_id","INT"),
        ("","company_name","VARCHAR(50)"),("","action","ENUM"),("","table_name","VARCHAR(50)"),
        ("","record_id","INT"),("","old_values","JSON"),("","new_values","JSON"),
        ("","ip_address","VARCHAR(45)"),("","user_agent","VARCHAR(255)"),("","created_at","DATETIME"),
    ],
    "user_sessions": [
        ("PK","id","INT AI"),("FK","user_id","INT"),("UK","session_token","VARCHAR(255)"),
        ("","ip_address","VARCHAR(45)"),("","user_agent","VARCHAR(255)"),("","is_active","TINYINT(1)"),
        ("","expires_at","DATETIME"),("","created_at","DATETIME"),
    ],
    "user_permissions": [
        ("PK","id","INT AI"),("FK","user_id","INT"),("","permission_key","VARCHAR(100)"),
        ("","permission_value","TINYINT(1)"),("","created_at","DATETIME"),("","updated_at","DATETIME"),
    ],
    "password_resets": [
        ("PK","id","INT AI"),("","email","VARCHAR(100)"),("UK","token","VARCHAR(255)"),
        ("FK","company_id","INT"),("","is_used","TINYINT(1)"),("","expires_at","DATETIME"),
        ("","ip_address","VARCHAR(45)"),("","created_at","DATETIME"),
    ],
}

pfx = {
    "companies":"c","users":"u","products":"p","koch_quotations":"kq",
    "sliders":"sl","partners":"pa","featured_products":"fp","contact_messages":"cm",
    "system_settings":"ss","truck_types":"tt","notifications":"no","activity_logs":"al",
    "user_sessions":"us","user_permissions":"up","password_resets":"pr",
}

# ============================================================
# LAYOUT - Optimized grid with generous spacing
# companies (center-top hub), users (center-middle hub)
# ============================================================
GAP_X = 120  # horizontal gap between tables
GAP_Y = 140  # vertical gap between rows
COL = [40, 40+TBL_W+GAP_X, 40+2*(TBL_W+GAP_X), 40+3*(TBL_W+GAP_X), 40+4*(TBL_W+GAP_X)]
# COL = [40, 400, 760, 1120, 1480]
R0Y = 40
R1Y = 580     # enough room for tallest row-0 table (sliders=11 rows=314h) + gap
R2Y = 1180    # enough room for tallest row-1 table (koch_quotations=18 rows=496h) + gap

pos = {
    # --- ROW 0: company-connected content tables ---
    "sliders":            (COL[0], R0Y),
    "partners":           (COL[1], R0Y),
    "companies":          (COL[2], R0Y),
    "featured_products":  (COL[3], R0Y),
    "contact_messages":   (COL[4], R0Y),
    # --- ROW 1: core business + company-connected ---
    "products":           (COL[0], R1Y),
    "koch_quotations":    (COL[1], R1Y),
    "users":              (COL[2], R1Y),
    "system_settings":    (COL[3], R1Y),
    "truck_types":        (COL[4], R1Y),
    # --- ROW 2: user-connected system tables ---
    "notifications":      (COL[0], R2Y),
    "activity_logs":      (COL[1], R2Y),
    "user_sessions":      (COL[2], R2Y),
    "user_permissions":   (COL[3], R2Y),
    "password_resets":    (COL[4], R2Y),
}

# ============================================================
# RELATIONSHIPS
# (fk_table, pk_table, fk_row_index, pk_row_index=0 for PK)
# fk_row_index: index of the FK field in fk_table
# Source = FK side (Many), Target = PK side (One)
# startArrow=ERmany (at FK/source), endArrow=ERmandOne (at PK/target)
# ============================================================
rels = [
    # --- FK -> companies ---
    ("users",             "companies", 1, 0),   # users.company_id
    ("koch_quotations",   "companies", 2, 0),   # kq.company_id
    ("sliders",           "companies", 1, 0),   # sliders.company_id
    ("partners",          "companies", 1, 0),   # partners.company_id
    ("featured_products", "companies", 1, 0),   # fp.company_id
    ("contact_messages",  "companies", 1, 0),   # cm.company_id
    ("system_settings",   "companies", 1, 0),   # ss.company_id
    ("truck_types",       "companies", 1, 0),   # tt.company_id
    ("notifications",     "companies", 2, 0),   # notifications.company_id
    ("activity_logs",     "companies", 2, 0),   # al.company_id
    ("password_resets",   "companies", 3, 0),   # pr.company_id
    # --- FK -> users ---
    ("koch_quotations",   "users",    1, 0),    # kq.user_id
    ("koch_quotations",   "users",    4, 0),    # kq.quoted_by
    ("notifications",     "users",    1, 0),    # notifications.user_id
    ("activity_logs",     "users",    1, 0),    # al.user_id
    ("user_sessions",     "users",    1, 0),    # us.user_id
    ("user_permissions",  "users",    1, 0),    # up.user_id
    # --- FK -> products ---
    ("koch_quotations",   "products", 3, 0),    # kq.product_id
]

def tbl_height(name):
    return HDR_H + len(tables[name]) * ROW_H

def row_id(tbl, idx):
    return f"{pfx[tbl]}_r{idx}"

# ============================================================
# XML BUILDERS
# ============================================================
def mk_table(name, fields):
    p = pfx[name]
    tid = f"t_{p}"
    x, y = pos[name]
    h = tbl_height(name)
    L = []
    L.append(f'        <mxCell id="{tid}" value="{name}" style="shape=table;startSize={HDR_H};container=1;collapsible=0;childLayout=tableLayout;fixedRows=1;rowLines=1;fontStyle=1;align=center;resizeLast=1;fontSize=13;fillColor=#dae8fc;strokeColor=#6c8ebf;" vertex="1" parent="1">')
    L.append(f'          <mxGeometry x="{x}" y="{y}" width="{TBL_W}" height="{h}" as="geometry"/>')
    L.append(f'        </mxCell>')
    for i,(kt,fn,dt) in enumerate(fields):
        rid = f"{p}_r{i}"
        c0 = f"{p}_r{i}c0"
        c1 = f"{p}_r{i}c1"
        yo = HDR_H + i * ROW_H
        pk = (kt == "PK")
        fill = "fillColor=#dae8fc;" if pk else "fillColor=none;"
        sc = "strokeColor=#6c8ebf;" if pk else ""
        bot = "bottom=1;" if pk else "bottom=0;"
        L.append(f'        <mxCell id="{rid}" value="" style="shape=tableRow;horizontal=0;startSize=0;swimlaneHead=0;swimlaneBody=0;{fill}collapsible=0;dropTarget=0;points=[[0,0.5],[1,0.5]];portConstraint=eastwest;fontSize=11;top=0;left=0;right=0;{bot}{sc}" vertex="1" parent="{tid}">')
        L.append(f'          <mxGeometry y="{yo}" width="{TBL_W}" height="{ROW_H}" as="geometry"/>')
        L.append(f'        </mxCell>')
        L.append(f'        <mxCell id="{c0}" value="{kt}" style="shape=partialRectangle;connectable=0;{fill}top=0;left=0;bottom=0;right=0;fontStyle=1;overflow=hidden;fontSize=10;{sc}" vertex="1" parent="{rid}">')
        L.append(f'          <mxGeometry width="30" height="{ROW_H}" as="geometry"/>')
        L.append(f'        </mxCell>')
        ul = "fontStyle=4;" if pk else ""
        L.append(f'        <mxCell id="{c1}" value="{fn} : {dt}" style="shape=partialRectangle;connectable=0;{fill}top=0;left=0;bottom=0;right=0;overflow=hidden;fontSize=10;{sc}{ul}" vertex="1" parent="{rid}">')
        L.append(f'          <mxGeometry x="30" width="{TBL_W-30}" height="{ROW_H}" as="geometry"/>')
        L.append(f'        </mxCell>')
    return L


def mk_rel(idx, fk_tbl, pk_tbl, fk_ri, pk_ri):
    """Build relationship line with correct Crow's Foot.
    Source = FK row (Many side), Target = PK row (One side).
    portConstraint=eastwest on rows → only left(0) or right(1) exits allowed.
    """
    src = row_id(fk_tbl, fk_ri)
    tgt = row_id(pk_tbl, pk_ri)
    sx, _ = pos[fk_tbl]
    tx, _ = pos[pk_tbl]
    src_cx = sx + TBL_W / 2
    tgt_cx = tx + TBL_W / 2
    dx = tgt_cx - src_cx

    if dx > 60:
        # Target is to the right → exit right, enter left
        ex, nx = "1", "0"
    elif dx < -60:
        # Target is to the left → exit left, enter right
        ex, nx = "0", "1"
    else:
        # Same column → both exit left side (C-shape routing on left)
        ex, nx = "0", "0"

    rid = f"rel_{idx}"
    style = (
        f"edgeStyle=orthogonalEdgeStyle;rounded=1;orthogonalLoop=1;jettySize=auto;html=1;"
        f"strokeColor=#6c8ebf;strokeWidth=1;"
        f"startArrow=ERmany;startFill=0;"
        f"endArrow=ERmandOne;endFill=0;"
        f"exitX={ex};exitY=0.5;exitDx=0;exitDy=0;"
        f"entryX={nx};entryY=0.5;entryDx=0;entryDy=0;"
    )
    L = []
    L.append(f'        <mxCell id="{rid}" value="" style="{style}" edge="1" parent="1" source="{src}" target="{tgt}">')
    L.append(f'          <mxGeometry relative="1" as="geometry"/>')
    L.append(f'        </mxCell>')
    return L


# ============================================================
# MAIN
# ============================================================
out = []
out.append('<mxfile host="draw.io">')
out.append('  <diagram name="Physical ER Diagram - KOCH System" id="koch_er">')
out.append('    <mxGraphModel dx="2800" dy="2200" grid="1" gridSize="10" guides="1" tooltips="1" connect="1" arrows="1" fold="1" page="0" pageScale="1" pageWidth="3300" pageHeight="2400" math="0" shadow="0">')
out.append('      <root>')
out.append('        <mxCell id="0"/>')
out.append('        <mxCell id="1" parent="0"/>')

for name in ["companies","users","products","koch_quotations","sliders","partners",
             "featured_products","contact_messages","system_settings","truck_types",
             "notifications","activity_logs","user_sessions","user_permissions","password_resets"]:
    out.extend(mk_table(name, tables[name]))

for i, (fk,pk,fri,pri) in enumerate(rels):
    out.extend(mk_rel(i+1, fk, pk, fri, pri))

out.append('      </root>')
out.append('    </mxGraphModel>')
out.append('  </diagram>')
out.append('</mxfile>')

with open('/Applications/XAMPP/xamppfiles/htdocs/Final-Project/diagrams-koch-main/er_diagram_physical.drawio', 'w', encoding='utf-8') as f:
    f.write('\n'.join(out))

print(f"Generated {len(out)} lines")
print("Done!")
