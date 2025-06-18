import streamlit as st
import pandas as pd
from datetime import datetime
import io
import os

st.set_page_config(page_title="📧 Email Inventory App", layout="wide")
st.title("📧 Email Inventory Management with Auto-Status & Duplicate Removal")

# Load reference email lists (single-column files)
def load_email_list(file_name):
    if os.path.exists(file_name):
        df = pd.read_excel(file_name)
        return set(df.iloc[:, 0].dropna().str.lower())
    return set()

# Reference sets
bounced_emails = load_email_list("Bounced.xlsx")
unsubscribed_emails = load_email_list("Unsubscribe.xlsx")
invalid_emails = load_email_list("Invalid.xlsx")

# Upload the main email inventory
uploaded_file = st.file_uploader("📂 Upload your main Excel file", type=["xlsx"])

if uploaded_file:
    df = pd.read_excel(uploaded_file)

    # Ensure required columns exist
    for col in ['Status', 'Last Updated']:
        if col not in df.columns:
            df[col] = ''

    # ──────────────────────────────────────────────
    # 🔁 DUPLICATE EMAIL CHECK & REMOVAL
    # ──────────────────────────────────────────────
    initial_count = len(df)
    df['Email ID Clean'] = df['Email ID'].astype(str).str.lower().str.strip()
    duplicate_df = df[df.duplicated('Email ID Clean', keep=False)]

    # Show duplicates before dropping
    if not duplicate_df.empty:
        st.warning(f"⚠️ Found {len(duplicate_df)} rows with duplicate Email IDs.")
        st.markdown("### 🧩 Duplicate Email IDs (Before Removal)")
        st.dataframe(duplicate_df.drop(columns=['Email ID Clean']), use_container_width=True)

        # Download duplicates
        duplicate_output = io.BytesIO()
        with pd.ExcelWriter(duplicate_output, engine='openpyxl') as writer:
            duplicate_df.drop(columns=['Email ID Clean']).to_excel(writer, index=False, sheet_name='Duplicates')
        st.download_button(
            label="📥 Download Duplicate List",
            data=duplicate_output.getvalue(),
            file_name="duplicate_emails.xlsx",
            mime="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
        )

    # Drop duplicate email IDs, keep first
    df = df.drop_duplicates(subset='Email ID Clean', keep='first')
    df.reset_index(drop=True, inplace=True)
    removed_count = initial_count - len(df)

    if removed_count > 0:
        st.info(f"🧹 Removed {removed_count} duplicate rows. Only unique email IDs retained.")
    else:
        st.success("✅ No duplicate Email IDs found. All records are unique.")

    # ──────────────────────────────────────────────
    # ✅ AUTO-TAG EMAIL STATUS
    # ──────────────────────────────────────────────
    for i in range(len(df)):
        email = df.iloc[i]['Email ID Clean']
        idx = df.index[i]

        if email in bounced_emails:
            df.at[idx, 'Status'] = 'Bounceback'
        elif email in unsubscribed_emails:
            df.at[idx, 'Status'] = 'Unsubscribed'
        elif email in invalid_emails:
            df.at[idx, 'Status'] = 'Invalid'

        if df.at[idx, 'Status']:
            df.at[idx, 'Last Updated'] = datetime.now().strftime("%Y-%m-%d")

    st.success("✅ Emails auto-tagged using Bounced, Unsubscribed, and Invalid lists.")

    # ──────────────────────────────────────────────
    # ✏️ EDIT EMAIL STATUS
    # ──────────────────────────────────────────────
    st.markdown("### ✏️ Review or Edit Email Status")
    status_options = ['Valid', 'Bounceback', 'Unsubscribed', 'Invalid', '']

    for i in range(len(df)):
        cols = st.columns([2, 2, 2, 3, 2, 2, 2])
        cols[0].write(df.iloc[i]['First Name'])
        cols[1].write(df.iloc[i]['Second Name'])
        cols[2].write(df.iloc[i]['Company Name'])
        cols[3].write(df.iloc[i]['Email ID'])
        cols[4].write(df.iloc[i]['Phone'])
        cols[5].write(df.iloc[i]['User'])

        current_status = df.iloc[i]['Status'] if df.iloc[i]['Status'] in status_options else ''
        new_status = cols[6].selectbox(
            "", status_options,
            index=status_options.index(current_status),
            key=f"status_{i}"
        )

        if new_status != df.iloc[i]['Status']:
            df.at[df.index[i], 'Status'] = new_status
            df.at[df.index[i], 'Last Updated'] = datetime.now().strftime("%Y-%m-%d")

    # ──────────────────────────────────────────────
    # 🔍 FILTER VIEW
    # ──────────────────────────────────────────────
    st.markdown("### 🔍 Filter by Status")
    selected_status = st.selectbox("Show only", ["All"] + status_options[:-1])  # Exclude blank
    filtered_df = df[df['Status'] == selected_status] if selected_status != "All" else df

    st.dataframe(filtered_df.drop(columns=['Email ID Clean']), use_container_width=True)

    # ──────────────────────────────────────────────
    # 📥 EXPORT FILTERED DATA
    # ──────────────────────────────────────────────
    st.markdown("### 📤 Download Filtered Excel")
    output = io.BytesIO()
    with pd.ExcelWriter(output, engine='openpyxl') as writer:
        filtered_df.drop(columns=['Email ID Clean']).to_excel(writer, index=False, sheet_name='EmailInventory')
    st.download_button(
        label="⬇️ Download Filtered List",
        data=output.getvalue(),
        file_name="email_inventory_filtered.xlsx",
        mime="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
    )

else:
    st.info("👆 Please upload your main Excel (.xlsx) file to begin.")
