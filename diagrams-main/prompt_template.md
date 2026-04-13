![alt text](image.png)## ✅ Prompt (Optimized Version)

**Prompt:**

**Role:**
Act as an **Expert System Analyst** and **Draw.io XML Architect**

**Task:**
Generate **raw XML code** for a `.drawio` file based on the system flow and user permissions.
Analyze the process and design a **[ระบุประเภทแผนภาพ เช่น Sequence Diagram / Use Case Diagram / Activity Diagram]** for the following system:

**System Flow:**

1. [ระบุขั้นตอนที่ 1 เช่น User กรอกรหัสผ่าน]
2. [ระบุขั้นตอนที่ 2 เช่น System ตรวจสอบรหัสผ่านกับ Database]
3. [ระบุขั้นตอนที่ 3 …]

---

## 🔧 Technical Requirements:

* Output must be **complete raw XML** in `<mxfile>` and `<mxGraphModel>` format
  → Must be directly usable in **draw.io** or saved as `.drawio`

* ❌ **Do NOT wrap output in Markdown** (no ```xml blocks)
  → Output pure XML only to prevent copy errors

* XML structure must include:

  * `<mxCell id="0" />`
  * `<mxCell id="1" parent="0" />`
  * Both must be inside `<root>`

* Layout requirements:

  * Proper spacing (no overlapping elements)
  * Define clear `x`, `y`, `width`, `height`
  * Diagram must be visually organized

* Typography:

  * All labels must use **fontSize = 14px**

* Diagram-specific rules:

  * If **Sequence Diagram**:

    * Include **Lifelines**
    * Include **Activation Boxes** correctly aligned with interactions

---
