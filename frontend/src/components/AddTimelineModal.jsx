import { ErrorMessage, Field, Form, Formik } from "formik";
import * as Yup from "yup";
import { IoClose } from "react-icons/io5";
import { Spinner } from "./Spinner";
import { toast } from "react-toastify";
import { addTimelineAsync, updateTimelineAsync } from "../services";
import { TimlineTableRow } from "./TimlineTableRow";
import { useParams } from "react-router-dom";
import { FormikDatePicker } from "./FormikDatePicker.jsx";
import { format } from "date-fns";
import { enUS } from "date-fns/locale";

export const AddTimelineModal = ({ open, setOpen, refetch, data }) => {
  let initialValues = {
    date: null,
    description: "",
  };

  const { id } = useParams();

  const handleClose = () => {
    setOpen(false);
  };

  const validationSchema = Yup.object().shape({
    date: Yup.date().required("Date is required"),
    description: Yup.string()
      .required("Caption is required")
      .min(2, "Caption should be at least 6 characters long"),
  });

  const handleSubmit = async (values, { setSubmitting, resetForm }) => {
    setSubmitting(true);
    const { uuid, date, description } = values;
    try {
      const payload = {
        description,
        date: format(date, "yyyy-MM-dd", { locale: enUS }),
      };
      if (uuid) {
        const { data } = await updateTimelineAsync(uuid, payload);
        if (data.status == 201) {
          toast.success("Event updated successfully.");
          refetch();
        } else {
          toast.error(data.messsage);
        }
      } else {
        const { data } = await addTimelineAsync(id, payload);
        if (data.status == 201) {
          toast.success(`New event added successfully.`);
          refetch();
        } else {
          toast.error(data.messsage);
        }
      }
    } catch (error) {
      console.log(error);
    } finally {
      setSubmitting(false);
      resetForm();
      setOpen(false);
    }
  };
  return (
    <Formik
      enableReinitialize
      validateOnMount
      validateOnChange
      validateOnBlur
      initialValues={initialValues}
      onSubmit={handleSubmit}
      validationSchema={validationSchema}
    >
      {({
        resetForm,
        isValid,
        handleBlur,
        isSubmitting,
        values,
        setFieldValue,
      }) => (
        <Form>
          <div
            className={
              `${
                data?.dark_theme
                  ? "bg-[#242526] "
                  : "bg-white border border-[2px]"
              } backdrop-filter backdrop-blur-sm bg-opacity-60 overflow-x-hidden fixed top-0 right-0 left-0 bottom-0 max-h-full !z-50 flex justify-center items-center w-full md:inset-0 ` +
              (open ? "block" : "hidden")
            }
          >
            <div className="relative p-4 w-full max-w-[500px] md:max-w-6xl max-h-full">
              <div
                className={`${
                  data?.dark_theme ? "bg-[#242526]" : "bg-white"
                } relative rounded-lg shadow`}
              >
                <div className="flex items-center justify-center p-4 border-b relative border-white/5 rounded-t">
                  <h3 className="text-xl font-semibold text-center w-full">
                    Add Timeline
                  </h3>
                  <div className="absolute top-0 right-0 left-0 bottom-0 flex items-center justify-end p-4">
                    <button
                      onClick={() => {
                        handleClose();
                        resetForm();
                      }}
                      type="button"
                      className="text-gray-400 transition-all duration-200 ease-in-out bg-white bg-opacity-20 hover:bg-opacity-25 active:bg-opacity-30 rounded-full text-sm w-8 h-8 ms-auto inline-flex justify-center items-center"
                    >
                      <IoClose size={24} />
                      <span className="sr-only">Close modal</span>
                    </button>
                  </div>
                </div>
                <div className="flex flex-col-reverse lg:flex-row max-h-[450px] overflow-auto hide-scrollbar gap-6 p-4 md:p-6">
                  <div id="timeline-form" className="space-y-4 flex-grow">
                    <div>
                      <label
                        htmlFor="date"
                        className="block mb-2 text-sm font-medium text-white"
                      >
                        Select Life Event Date
                      </label>
                      <Field name={"date"} component={FormikDatePicker} />
                      <ErrorMessage
                        className="text-red-400 text-xs"
                        name="date"
                        component="div"
                      />
                    </div>
                    <div>
                      <label
                        className="block mb-2 text-sm font-medium text-white"
                        htmlFor="youtube"
                      >
                        Write life event here
                      </label>
                      <textarea
                        rows={4}
                        maxLength={4000}
                        className={`${
                          data?.dark_theme
                            ? "bg-[#333333] border-white/20 placeholder-gray-400 text-white"
                            : "bg-[#F1F1F1] border-white/20 placeholder text-black"
                        } border outline-none text-sm rounded-lg block w-full p-2.5 `}
                        placeholder="Write life event here..."
                        value={values.description}
                        onChange={(e) =>
                          setFieldValue("description", e.target.value)
                        }
                        onBlur={handleBlur}
                        name="description"
                      />
                      <ErrorMessage
                        className="text-red-400 text-xs"
                        name="description"
                        component="div"
                      />
                    </div>

                    <button
                      disabled={!isValid || isSubmitting}
                      type="submit"
                      className="flex items-center justify-center disabled:text-gray-400 disabled:bg-white/20 disabled:bg-opacity-30 hover:bg-white/70 w-full text-[#333333] bg-white/90 active:bg-white/90 font-medium rounded-lg px-5 py-2 text-center transition-all duration-200 ease-in-out"
                    >
                      {isSubmitting ? <Spinner /> : "Submit"}
                    </button>
                  </div>
                  <div className="lg:flex-grow-[3]">
                    <div className="relative overflow-x-auto shadow-md sm:rounded-lg">
                      <table className="w-full text-sm text-left rtl:text-right text-gray-400">
                        <thead className="text-xs uppercase bg-white/10 text-gray-200">
                          <tr>
                            <th scope="col" className="px-6 py-3">
                              Date
                            </th>
                            <th scope="col" className="px-6 py-3">
                              Life Event
                            </th>
                            <th scope="col" className="px-6 py-3">
                              Action
                            </th>
                          </tr>
                        </thead>
                        <tbody>
                          {data?.Timelines?.map((timeline) => (
                            <TimlineTableRow
                              theme={data?.dark_theme}
                              refetch={refetch}
                              timeline={timeline}
                              key={timeline.id}
                              setFieldValue={setFieldValue}
                            />
                          ))}
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </Form>
      )}
    </Formik>
  );
};
