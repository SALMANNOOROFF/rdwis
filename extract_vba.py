import win32com.client
import os

def export_vba_code(db_path):
    try:
        # Create Access Application object
        access = win32com.client.Dispatch("Access.Application")
        
        # Open the database
        access.OpenCurrentDatabase(db_path)
        
        # Create a directory for exported code
        export_dir = "exported_vba"
        if not os.path.exists(export_dir):
            os.makedirs(export_dir)
            
        print(f"Exporting VBA from {db_path}...")
        
        # Get the project
        project = access.VBE.ActiveVBProject
        
        for component in project.VBComponents:
            # component.Type 1 = Module, 2 = Class, 100 = Form/Report
            comp_type = component.Type
            name = component.Name
            
            # Export the code
            extension = ".bas"
            if comp_type == 2: extension = ".cls"
            elif comp_type == 100: extension = ".frm"
            
            file_path = os.path.join(export_dir, f"{name}{extension}")
            try:
                component.Export(os.path.abspath(file_path))
                print(f"Exported: {name}")
            except Exception as e:
                print(f"Failed to export {name}: {e}")
                
        access.Quit()
        print("Done.")
        
    except Exception as e:
        print(f"Error: {e}")

if __name__ == "__main__":
    db_file = os.path.abspath("dev.accdb")
    if os.path.exists(db_file):
        export_vba_code(db_file)
    else:
        print(f"File not found: {db_file}")
